<?php
namespace Framework\Twig;

/**
 * Serie d extensions concernant les textes
 * Class TextExtension
 * @package Framework\Twig
 */
class TextExtension extends \Twig_Extension
{
    /**
     * @return \Twig_SimpleFilter
     */
    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter('excerpt', [$this, 'excerpt'])
        ];
    }

    /**
     * Renvoi un extrait du contenu
     * @param $content
     * @param int $maxLength
     * @return string
     */
    public function excerpt(?string $content, $maxLength = 100): string
    {
        if (is_null($content)) {
            return '';
        }
        if (mb_strlen($content) > $maxLength) {
            $excerpt = mb_substr($content, 0, $maxLength);
            $lastSpace = mb_strrpos($excerpt, ' ');

            return $excerpt = mb_substr($content, 0, $lastSpace) . '...';
        }

        return $content;
    }
}
