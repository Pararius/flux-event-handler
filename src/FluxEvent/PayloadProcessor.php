<?php
declare(strict_types=1);

namespace TreeHouse\FluxEvent;

class PayloadProcessor
{
    /**
     * @throws \RuntimeException
     */
    public function process(string $json): array
    {
        $payload = json_decode($json);
        $response = [
            'title' => $payload->Title,
            'titleLink' => $payload->TitleLink,
            'changes' => []
        ];

        switch ($payload->Type) {
            case 'commit':
                foreach ($payload->Event->metadata->spec->spec->Changes as $workload) {
                    $oldImage = $workload->Container->Image;
                    $newImage = $workload->ImageID;

                    if (!array_key_exists($oldImage, $response['changes'])) {
                        $response['changes'][$oldImage] = $newImage;
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

        return $response;
    }
}
