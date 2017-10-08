<?php
namespace Framework\Validator;

class ValidationError
{
    private $key;
    private $rule;

    private $messages = [
        'required' => 'Le champs %s est requis.',
        'empty' => 'Le champs %s ne doit pas etre vide.',
        'slug' => 'Le champs %s n\'est pas valid.',
        'minLength' => 'Le champs %s doit contenir plus de %d caracteres',
        'maxLength' => 'Le champs %s doit contenir moins de %d caracteres',
        'betweenLength' => 'Le champs %s doit contenir entrer %d et %d caracteres',
        'datetime' => 'Le champs %s doit etre une date valide (%s)'
    ];
    /**
     * @var array
     */
    private $attributes;

    public function __construct(string $key, string $rule, array $attributes = [])
    {
        $this->key = $key;
        $this->rule = $rule;
        $this->attributes = $attributes;
    }

    public function __toString()
    {
        $params = array_merge([$this->messages[$this->rule], $this->key], $this->attributes);

        return (string) call_user_func_array('sprintf', $params);
    }
}
