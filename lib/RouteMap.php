<?php

namespace Meetings;

use Meetings\Providers\StudipServices;

class RouteMap
{
    public function __construct(\Slim\App $app)
    {
        $this->app = $app;
    }

    public function __invoke()
    {
        $container = $this->app->getContainer();

        $this->app->group('', [$this, 'authenticatedRoutes'])
            ->add(new Middlewares\Authentication($container[StudipServices::AUTHENTICATOR]))
            ->add(new Middlewares\RemoveTrailingSlashes);

        $this->app->group('', [$this, 'adminRoutes'])
            ->add(new Middlewares\AdminPerms($container))
            ->add(new Middlewares\Authentication($container[StudipServices::AUTHENTICATOR]))
            ->add(new Middlewares\RemoveTrailingSlashes);

        $this->app->get('/discovery', Routes\DiscoveryIndex::class);

        $this->app->group('', [$this, 'unauthenticatedRoutes'])
            ->add(new Middlewares\RemoveTrailingSlashes);
    }

    public function authenticatedRoutes()
    {
        global $user;

        $this->app->get('/user', Routes\Users\UsersShow::class);

        //Routes for rooms in seminar
        $this->app->get('/course/{cid}/rooms', Routes\Rooms\RoomsList::class);
        $this->app->get('/course/{cid}/config', Routes\Config\ConfigListCourse::class);

        $this->app->get('/rooms/{room_id}', Routes\Rooms\RoomShow::class);
        $this->app->get('/rooms/{cid}/{room_id}/status', Routes\Rooms\RoomRunning::class);
        $this->app->get('/rooms/{cid}/info', Routes\Rooms\RoomInfo::class);

        //Route for joining a meeting
        $this->app->get('/rooms/join/{cid}/{room_id}', Routes\Rooms\RoomJoin::class);

        //Routes for recordings
        $this->app->get('/rooms/{cid}/{room_id}/recordings', Routes\Recordings\RecordingList::class);

        //following requests contain validation of permissions
        // rooms with perm
        $this->app->post('/rooms', Routes\Rooms\RoomAdd::class);
        $this->app->put('/rooms/{room_id}', Routes\Rooms\RoomEdit::class);
        $this->app->delete('/rooms/{cid}/{room_id}', Routes\Rooms\RoomDelete::class);

        //generate guest invitation link
        $this->app->get('/rooms/join/{cid}/{room_id}/{guest_name}/guest', Routes\Rooms\RoomJoinGuest::class);
        $this->app->get('/rooms/invitationLink/{cid}/{room_id}',  Routes\Rooms\RoomInvitationLink::class);
        //recordings with perm
        $this->app->get('/recordings/{cid}/{room_id}/{recordings_id}', Routes\Recordings\RecordingShow::class);
        $this->app->delete('/recordings/{cid}/{room_id}/{recordings_id}', Routes\Recordings\RecordingDelete::class);

        //routes for feedback
        $this->app->post('/feedback', Routes\Feedback\FeedbackSubmit::class);
        $this->app->post('/feedback/uploadTest', Routes\Feedback\UploadTest::class);

        //routes for folders
        $this->app->get('/folders/{cid}/{folder_id}', Routes\Folder\FolderList::class);
        $this->app->post('/folders/new_folder', Routes\Folder\FolderCreate::class);
    }

    public function adminRoutes()
    {
        //configs
        $this->app->get('/config/list', Routes\Config\ConfigList::class);
        $this->app->get('/config/{id}', Routes\Config\ConfigShow::class);
        $this->app->post('/config', Routes\Config\ConfigAdd::class);
        $this->app->put('/config/{id}', Routes\Config\ConfigEdit::class);
        $this->app->delete('/config/{id}', Routes\Config\ConfigDelete::class);

    }

    public function unauthenticatedRoutes() 
    {
        $this->app->get('/slides/{meeting_id}/{slide_id}/{token}', Routes\Slides\SlidesShow::class);
    }
}
