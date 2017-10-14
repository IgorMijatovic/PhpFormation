<?php
namespace Framework\Twig;


class FormExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('field', [$this, 'field'], [
                'is_safe'       => ['html'],
                'needs_context' => true
            ])
        ];
    }

    /**
     * Genere le code html d un champs
     * @param array $context
     * @param string $key
     * @param $value
     * @param null|string|null $label
     * @param array $options
     * @return string
     */
    public function field(array $context, string $key, $value, ?string $label = null, array $options = []): string
    {
        $type = $options['type'] ?? 'text';
        $error = $this->getErrorHtml($context, $key);
        $class = 'form-group';
        $value = $this->convertValue($value);
        $attributes = [
            'class' => trim('form-control ' . ($options['class'] ?? '')),
            'name'  => $key,
            'id'    => $key
        ];

        if($error){
            $class .= ' has-danger';
            $attributes['class'] .= ' form-control-danger';
        }
        if($type === 'textarea'){
            $input = $this->textarea($value, $attributes);
        } else {
            $input = $this->input($value, $attributes);
        }
        return "            
            <div class=\"" . $class . "\">
                <label for=\"name\">{$label}</label>
                {$input}
                {$error}
            </div>";
    }

    /**
     * Génère un <input>
     * @param null|string $value
     * @param array $attributes
     * @return string
     */
    private function input(?string $value, array $attributes): string
    {
        return "<input type=\"text\" " . $this->getHtmlFromArray($attributes) . " value=\"{$value}\">";
    }

    /**
     * Génère un <textarea>
     * @param null|string $value
     * @param array $attributes
     * @return string
     */
    private function textarea(?string $value, array $attributes): string
    {
        return "<textarea " . $this->getHtmlFromArray($attributes) . ">{$value}</textarea>";
    }

    private function getErrorHtml($context, $key){
        $error = $context['errors'][$key] ?? false;
        if($error){

            return "<small class=\"form-text text-muted\">{$error}</small>";
        }

        return "";
    }

    /**
     * Transforme un tableau $clef => $valeur en attribut HTML
     * @param array $attributes
     * @return string
     */
    private function getHtmlFromArray(array $attributes)
    {
        return implode(' ', array_map(function ($key, $value) {
            return "$key=\"$value\"";
        }, array_keys($attributes), $attributes));
    }

    private function convertValue($value): string
    {
        if($value instanceof \DateTime){

            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }
}