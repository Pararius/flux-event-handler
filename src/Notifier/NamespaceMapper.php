<?php
declare(strict_types=1);

namespace TreeHouse\Notifier;

/**
 * Class NamespaceMapper
 * @package TreeHouse\Notifier
 *
 * Allows you to map certain namespaces to specific Notification channels.
 * The syntax of the map should be similar to fluxcloud's map. For example for the SlackNotifier:
 *
 * #project=foo,#operations=*
 *
 * Would send all notifications regarding the "foo" namespace to the #project channel.
 * The #operations channel would receive all notifications for any namespace.
 */
class NamespaceMapper
{
    /** @var array */
    public $namespaceMap;

    /**
     * @throws \RuntimeException
     */
    public function __construct(string $mapping)
    {
        $namespaceMap = [];

        $mappings = explode(',', $mapping);
        foreach ($mappings as $map) {
            if (false === strpos($map, '=')) {
                throw new \RuntimeException(sprintf('Namespace mapping %s is invalid', $map));
            }

            list($channel, $namespace) = explode('=', $map);
            $namespaceMap[$channel] = $namespace;
        }

        $this->namespaceMap = $namespaceMap;
    }
}
