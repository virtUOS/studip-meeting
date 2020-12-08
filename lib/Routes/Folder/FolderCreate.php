<?php

namespace Meetings\Routes\Folder;

/**
 *
 * @author Farbod Zamani <zamani@elan-ev.de>
 */

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Meetings\Errors\AuthorizationFailedException;
use Meetings\MeetingsTrait;
use Meetings\MeetingsController;
use Meetings\Errors\Error;
use Exception;
use Meetings\Models\I18N as _;

use ElanEv\Model\MeetingCourse;
use ElanEv\Model\Meeting;

class FolderCreate extends MeetingsController
{
    use MeetingsTrait;
    /**
     * Returns the info about folders
     *
     * @param string $folder_id folder id
     * @param string $cid course id
     *
     *
     * @return json room parameter
     *
     * @throws \Error in case of failure or wrong folder data
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->getRequestData($request);
        try {
            $cid = filter_var($json['cid'], FILTER_SANITIZE_STRING);
            $parent_id = filter_var($json['parent_id'], FILTER_SANITIZE_STRING);
            $name = filter_var($json['name'], FILTER_SANITIZE_STRING);
            $desc = filter_var($json['desc'], FILTER_SANITIZE_STRING);
            $folder_type = filter_var($json['type'], FILTER_SANITIZE_STRING);

            if (empty($cid) || empty($parent_id) || empty($name) || empty($folder_type)) {
                return $this->createResponse([
                    'message'=> [
                        'text' => _('Fehler beim Anlegen des Ordners!'),
                        'type' => 'error'
                    ],
                ], $response);
                die;
            }

            $message = [
                'text' => _('Der Ordner wurde angelegt.'),
                'type' => 'success'
            ];
            if (!is_subclass_of($folder_type, 'FolderType')) {
                return $this->createResponse([
                    'message'=> [
                        'text' => _('Der gewünschte Ordnertyp ist ungültig!'),
                        'type' => 'error'
                    ],
                ], $response);
                die;
            }

            $parent_folder = \FileManager::getTypedFolder($parent_id);

            $new_folder = new $folder_type(
                ['range_id' => $parent_folder->range_id,
                 'range_type' => $parent_folder->range_type,
                 'parent_id' => $parent_folder->getId(),
                 'name' => $name,
                 'description' => $desc]
            );
            
            if ($new_folder instanceof \FolderType) {
                $new_folder->user_id = \User::findCurrent()->id;
                if (!$parent_folder->createSubfolder($new_folder)) {
                    $message = [
                        'text' => _('Fehler beim Anlegen des Ordners!'),
                        'type' => 'error'
                    ];
                }
            } else {
                $message = [
                    'text' => $result,
                    'type' => 'error'
                ];
            }


            return $this->createResponse([
                'message'=> $message,
            ], $response);
        } catch (Exception $e) {
            throw new Error($e->getMessage(), 404);
        }
    }

}
