<?php declare(strict_types=1);

namespace App\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;

class ValidatorFactoryResolvedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [ValidatorFactoryResolved::class];
    }

    public function process(object $event)
    {
        /**
         * @var ValidatorFactoryInterface $validatorFactory
         */
        $validatorFactory = $event->validatorFactory;
    }
}
