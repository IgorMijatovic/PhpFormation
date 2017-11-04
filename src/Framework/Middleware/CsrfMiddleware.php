<?php
namespace Framework\Middleware;

use Framework\Exception\CsrfInvalidException;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * techniquement un token est valable une fois
 * gledamo poslie svako post request dali form ima attribut csrf i dal je
 * csrf u session spassen, jer neko mote kopirat nas form i na svom
 * situ ga ausführen
 *
 * Class CsrfMiddleware
 * @package Framework\Middleware
 */
class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $formKey;

    private $session;
    /**
     * @var string
     */
    private $sessionKey;
    /**
     * @var int
     */
    private $limit;

    public function __construct(&$session, int $limit = 50, string $formKey = '_csrf', string $sessionKey = 'csrf')
    {
        $this->validSession($session);
        $this->session = &$session;
        $this->limit = $limit;
        $this->formKey = $formKey;
        $this->sessionKey = $sessionKey;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            $params = $request->getParsedBody() ?: [];
            if (!array_key_exists($this->formKey, $params)) {
                $this->reject();
            } else {
                $csrfList = $this->session[$this->sessionKey] ?? [];
                if (in_array($params[$this->formKey], $csrfList)) {
                    $this->useToken($params[$this->formKey]);

                    return $handler->handle($request);
                } else {
                    $this->reject();
                }
            }
        } else {
            return $handler->handle($request);
        }
    }

    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(16));
        $csrfList = $this->session[$this->sessionKey] ?? [];
        $csrfList[] = $token;
        $this->session[$this->sessionKey] = $csrfList;
        // avoir une nb limite des tokens
        $this->limitTokens();
        return $token;
    }

    private function reject(): void
    {
        throw new CsrfInvalidException();
    }

    /**
     * enelever token apres utilisation
     * @param $token
     */
    private function useToken($token): void
    {
        $tokens = array_filter($this->session[$this->sessionKey], function ($t) use ($token) {
            return $token !== $t;
        });
        $this->session[$this->sessionKey] = $tokens;
    }

    /**
     * limit le nombre de tokens dans la session
     */
    private function limitTokens(): void
    {
        $tokens = $this->session[$this->sessionKey] ?? [];
        if (count($tokens) > $this->limit) {
            array_shift($tokens);
        }
        $this->session[$this->sessionKey] = $tokens;
    }

    private function validSession($session)
    {
        if (!is_array($session) && !$session instanceof  \ArrayAccess) {
            throw new \TypeError('La session passe au middleware CSRF n\'est pas traitable comme un tableau');
        }
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->formKey;
    }
}
