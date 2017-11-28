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
        'datetime' => 'Le champs %s doit etre une date valide (%s)',
        'exists' => 'Le champs %s n existe pas sur dans la table %s',
        'unique' => 'Le champs %s doit etre unique',
        'filetype' => 'Le champs %s n est pas au format valide (%s)',
        'uploaded' => 'Vous devez uploader une image',
        'email' => 'Cette email ne semble valide',
        'confirm' => 'Vous n \'avez pa sconfirme le champs %s',
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
        if (!array_key_exists($this->rule, $this->messages)) {
            return "Le champs {$this->key} ne correspond pas a la regle {$this->rule}.";
        } else {
            $params = array_merge([$this->messages[$this->rule], $this->key], $this->attributes);

            return (string) call_user_func_array('sprintf', $params);
        }
    }
}
