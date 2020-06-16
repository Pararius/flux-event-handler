<?php
declare(strict_types=1);

namespace TreeHouse\Notifier;

/**
 * Class GithubMapper
 * @package TreeHouse\Notifier
 *
 * Allows you to map certain namespace/image tuples to specific Github projects.
 * Syntax: <namespace1>/<image1>=<githubproj1>[,<namespace2>/<image2>=<githubproj2>,...]
 *
 * Example:
 *
 * foo/bar=baz/qux
 *
 * When the image "bar" in the "foo" namespace gets updated, it will show a Github
 * link pointing to "qux" repository in the "baz" organization.
 */
class GithubMapper
{
    /** @var array */
    public $githubMap;

    /**
     * @throws \RuntimeException
     */
    public function __construct(?string $mapping)
    {
        $githubMap = [];

        $mappings = is_null($mapping) ? [] : explode(',', $mapping);
        foreach ($mappings as $map) {
            if (false === strpos($map, '=')) {
                throw new \RuntimeException(sprintf('Github mapping %s is invalid', $map));
            }

            list($image, $githubProject) = explode('=', $map);

            if (false === strpos($image, '/')) {
                throw new \RuntimeException(sprintf('Github mapping %s is invalid', $map));
            }

            $githubMap[$image] = $githubProject;
        }

        $this->githubMap = $githubMap;
    }
}
