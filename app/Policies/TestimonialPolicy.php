<?php

namespace App\Policies;

use App\Policies\Concerns\AuthorizesResource;

class TestimonialPolicy
{
    use AuthorizesResource;

    protected function resource(): string
    {
        return 'testimonials';
    }
}
