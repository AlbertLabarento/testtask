<?php 

namespace App\Containers\Services;

class EntityValidator
{
    private $validator;

    public function __construct()
    {
        $this->validator = app('validator');
    }

    public function hasError( \App\Database\Entities\Entity $entity )
    {
        $validate = $this->validator->make( $entity->toMailChimpArray(), $entity->getValidationRules() );

        if( $validate->fails() )
        {
            return [
                'message' => 'Invalid data given',
                'errors' => $validate->errors()->toArray()
            ];
        }
    }
}