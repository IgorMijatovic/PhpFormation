<?php
namespace Framework;

use Framework\Database\Table;
use Framework\Validator\ValidationError;
use Psr\Http\Message\UploadedFileInterface;

class Validator
{
    private const MIME_TYPES =[
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'pdf' => 'application/pdf',
        ];

    /**
     * @var array
     */
    private $params;
    /**
     * @var string[]
     */
    private $errors = [];

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Verifie que les champs sont present dans le tableau
     * @param \string[] ...$keys
     * @return Validator
     */
    public function required(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (is_null($value)) {
                $this->addError($key, 'required');
            }
        }

        return $this;
    }

    /**
     * Verifie que le champs n est pas vide
     * @param \string[] ...$keys
     * @return Validator
     */
    public function notEmpty(string ...$keys): self
    {
        foreach ($keys as $key) {
            $value = $this->getValue($key);
            if (is_null($value) || empty($value)) {
                $this->addError($key, 'required');
            }
        }

        return $this;
    }

    /**
     * Recupere les eerros
     * @return ValidationError[]
     */
    public function getErrors(): array
    {

        return $this->errors;
    }

    /**
     * Verifie que l'element est un slug
     * @param string $key
     * @return Validator
     */
    public function slug(string $key): self
    {
        $value = $this->getValue($key);
        $pattern = '/^[a-z0-9]+(-[a-z0-9]+)*$/';
        if (!is_null($value) && !preg_match($pattern, $value)) {
            $this->addError($key, 'slug');
        }

        return $this;
    }


    public function exists(string $key, string $table, \PDO $pdo): self
    {
        $value = $this->getValue($key);
        $statement = $pdo->prepare("SELECT id FROM {$table} WHERE id = ?");
        $statement->execute([$value]);

        if ($statement->fetchColumn() === false) {
            $this->addError($key, 'exists', [$table]);
        }

        return $this;
    }

    /**
     * verifie que la clef est unique das la base de donnes
     *
     * @param string $key
     * @param string|Table $table
     * @param \PDO $pdo
     * @param int|null|null $exclude
     * @return Validator
     */
    public function unique(string $key, $table, ?\PDO $pdo = null, ?int $exclude = null): self
    {
        if ($table instanceof Table) {
            $pdo = $table->getPdo();
            $table = $table->getTable();
        }
        $value = $this->getValue($key);
        $query = "SELECT id FROM {$table} WHERE $key = ?";
        $params = [$value];
        if ($exclude !== null) {
            $query .= " AND id != ?";
            $params[] = $exclude;
        }

        $statement = $pdo->prepare($query);
        $statement->execute($params);

        if ($statement->fetchColumn() !== false) {
            $this->addError($key, 'unique', [$value]);
        }

        return $this;
    }

    /**
     * Verifie si le fischer a bien ete uploade
     * @param string $key
     * @return Validator
     */
    public function uploaded(string $key): self
    {
        /**
         * @var UploadedFileInterface $file
         */
        $file = $this->getValue($key);
        if ($file === null && $file->getError() !== UPLOAD_ERR_OK) {
            $this->addError($key, 'uploaded');
        }

        return $this;
    }

    /**
     * @param string $key
     * @return Validator
     */
    public function email(string $key): self
    {
        $value = $this->getValue($key);
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($key, 'email');
        }
        return $this;
    }

    public function confirm(string $key): self
    {
        $value = $this->getValue($key);
        $valueConfirm = $this->getValue($key . '_confirm');
        if ($valueConfirm !== $value) {
            $this->addError($key, 'confirm');
        }
        return $this;
    }

    /**
     * verifie le format de fichier
     * @param string $key
     * @param array $extension
     * @return Validator
     */
    public function extension(string $key, array $extensions): self
    {
        /**
         * @var UploadedFileInterface $file
         */
        $file = $this->getValue($key);
        if ($file !== null && $file->getError() === UPLOAD_ERR_OK) {
            $type = $file->getClientMediaType();
            $extension = mb_strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            $expectedType = self::MIME_TYPES[$extension] ?? null;
            if (!in_array($extension, $extensions) || $expectedType !== $type) {
                $this->addError($key, 'filetype', [join(',', $extensions)]);
            }
        }

        return $this;
    }

    public function length(string $key, ?int $min, ?int $max = null): self
    {
        $value = $this->getValue($key);
        $length = mb_strlen($value);
        if (!is_null($min) &&
            !is_null($max) &&
            ($length < $min || $length > $max)
        ) {
            $this->addError($key, 'betweenLength', [$min, $max]);

            return $this;
        }

        if (!is_null($min) &&
            $length < $min
        ) {
            $this->addError($key, 'minLength', [$min]);

            return $this;
        }

        if (!is_null($max) &&
            $length > $max
        ) {
            $this->addError($key, 'maxLength', [$max]);
        }

        return $this;
    }

    public function dateTime(string $key, string $format = 'Y-m-d H:i:s'): self
    {
        $value = $this->getValue($key);
        $date = \DateTime::createFromFormat($format, $value);
        $errors = \DateTime::getLastErrors();
        //var_dump($errors);
        if ($errors['error_count'] > 0 || $errors['warning_count'] > 0 || $date === false) {
            $this->addError($key, 'datetime', [$format]);
        }
        return $this;
    }

    /**
     * Ajoute une erreur
     * @param string $key
     * @param string $rule
     * @param array $params
     * return void
     */
    private function addError(string $key, string $rule, array $attributes = []): void
    {
        $this->errors[$key] = new ValidationError($key, $rule, $attributes);
    }

    private function getValue(string $key)
    {
        if (array_key_exists($key, $this->params)) {
            return $this->params[$key];
        }

        return null;
    }

    public function isValid():bool
    {

        return empty($this->errors);
    }
}
