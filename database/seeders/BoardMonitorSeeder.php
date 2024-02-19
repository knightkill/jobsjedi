<?php

namespace Database\Seeders;

use App\Models\Board;
use Illuminate\Database\Seeder;

class BoardMonitorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $boards = [
            [
                'name' => 'LaraJobs',
                'slug' => 'laraJobs',
            ],
            [
                'name' => 'LinkedIn',
                'slug' => 'linkedIn',
            ],
            [
                'name' => 'JustRemote',
                'slug' => 'justRemote',
            ],
            [
                'name' => 'WorkingNomads',
                'slug' => 'workingNomads',
            ],
            [
                'name' => 'FlexBox',
                'slug' => 'flexBox',
            ],
            [
                'name' => 'RemoteOK',
                'slug' => 'remoteOK',
            ],
            [
                'name' => 'WeWorkRemotely',
                'slug' => 'weWorkRemotely',
            ],
            [
                'name' => 'RemoteCo',
                'slug' => 'remoteCo',
            ],
        ];

        foreach($boards as $board) {
            Board::updateOrCreate($board);
        }
    }
}
