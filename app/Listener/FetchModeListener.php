<?php declare(strict_types=1);

namespace App\Listener;

use PDO;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Database\Events\StatementPrepared;

/**
 * Class FetchModeListener
 *
 * @Listener
 * @package App\Listener
 */
class FetchModeListener implements ListenerInterface
{
    public function listen(): array
    {
        return [StatementPrepared::class];
    }

    public function process(object $event)
    {
        if ($event instanceof StatementPrepared) {
            $event->statement->setFetchMode(PDO::FETCH_ASSOC);
        }
    }
}