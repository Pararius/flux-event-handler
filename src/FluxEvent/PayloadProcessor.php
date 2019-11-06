<?php
declare(strict_types=1);

namespace TreeHouse\FluxEvent;

class PayloadProcessor
{
    /**
     * @throws \RuntimeException
     */
    public function process(string $json): ProcessedPayload
    {
        $payload = json_decode($json);

        $processedPayload = new ProcessedPayload();
        $processedPayload->title = $payload->Title;
        $processedPayload->titleLink = $payload->TitleLink;
        $processedPayload->changes = [];
        $processedPayload->namespaces = [];

        switch ($payload->Type) {
            case 'commit':
                foreach ($payload->Event->metadata->spec->spec->Changes as $workload) {
                    $oldImage = $workload->Container->Image;
                    $newImage = $workload->ImageID;

                    if (!array_key_exists($oldImage, $processedPayload->changes)) {
                        $processedPayload->changes[$oldImage] = $newImage;
                    }

                    if (!array_key_exists($oldImage, $processedPayload->namespaces)) {
                        $namespace = $this->findNamespace($payload, $oldImage);
                        $processedPayload->namespaces[$oldImage] = $namespace;
                    }
                }

                break;
            default:
                throw new \RuntimeException(sprintf(
                    'Unknown payload type %s triggered by %s',
                    $payload->Type ?? 'none',
                    $payload->TitleLink ?? 'unknown'
                ));
        }

        return $processedPayload;
    }

    private function findNamespace(object $payload, string $image): string
    {
        foreach ($payload->Event->metadata->result as $workload => $result) {
            if (!empty($result->PerContainer)) {
                foreach ($result->PerContainer as $container) {
                    if ($container->Current == $image) {
                        return substr($workload, 0, strpos($workload, ':'));
                    }
                }
            }
        }

        return 'unknown';
    }
}
