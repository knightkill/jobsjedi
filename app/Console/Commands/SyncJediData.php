<?php

namespace App\Console\Commands;

use App\Models\Board;
use App\Models\BoardSetting;
use App\Models\Monitor;
use App\Models\MonitorSetting;
use App\Models\User;
use Illuminate\Console\Command;

class SyncJediData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:jedi-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync config required for hardip user.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $allisone = [
            'boards' => [
                [
                    'name' => 'LaraJobs',
                    'slug' => 'laraJobs',
                    'description' => '',
                    'active' => true,
                    'board_settings' => [
                        [
                            'key' => 'filter_tech',
                            'description' => 'Filter Tech',
                            'required' => false,
                        ],
                    ]
                ],
                [
                    'name' => 'LinkedIn',
                    'slug' => 'linkedIn',
                    'description' => '',
                    'active' => true,
                    'board_settings' => []
                ],
                [
                    'name' => 'JustRemote',
                    'slug' => 'justRemote',
                    'description' => '',
                    'active' => true,
                    'board_settings' => []
                ],
                [
                    'name' => 'WorkingNomads',
                    'slug' => 'workingNomads',
                    'description' => '',
                    'active' => true,
                    'board_settings' => []
                ],
                [
                    'name' => 'FlexBox',
                    'slug' => 'flexBox',
                    'description' => '',
                    'active' => true,
                    'board_settings' => []
                ],
                [
                    'name' => 'RemoteOK',
                    'slug' => 'remoteOK',
                    'description' => '',
                    'active' => true,
                    'board_settings' => []
                ],
                [
                    'name' => 'WeWorkRemotely',
                    'slug' => 'weWorkRemotely',
                    'description' => '',
                    'active' => true,
                    'board_settings' => []
                ],
                [
                    'name' => 'RemoteCo',
                    'slug' => 'remoteCo',
                    'description' => '',
                    'active' => true,
                    'board_settings' => []
                ],
            ],
            'users' => [
                [
                    'name' => env('HARDIP_USER_NAME', 'Hardip'),
                    'email' => env('HARDIP_USER_EMAIL'),
                    'password' => env('HARDIP_USER_PASSWORD'),
                    'monitors' => [
                        [
                            'name' => 'Hardip\'s LaraJobs',
                            'board_slug' => 'laraJobs',
                            'slug' => 'hardip-larajobs',
                            'description' => 'LaraJobs monitor for Hardip',
                            'active' => true,
                            'filter' => [
                                [
                                    'operator' => 'or',
                                    'value' => [
                                        [
                                            "field" => "title",
                                            "operator" => "like",
                                            "value" => "backend"
                                        ],
                                        [
                                            "field" => "description",
                                            "operator" => "like",
                                            "value" => "backend"
                                        ]
                                    ]
                                ]
                            ],
                            'settings' => [
                                [
                                    'key' => 'filter_tech',
                                    'value' => 'laravel',
                                ],
                            ]
                        ],
                        [
                            'name' => 'Hardip\'s JustRemote',
                            'board_slug' => 'justRemote',
                            'slug' => 'hardip-justremote',
                            'description' => 'JustRemote monitor for Hardip',
                            'active' => true,
                            'filter' => [],
                            'settings' => []
                        ]
                    ]
                ]
            ]
        ];

        foreach ($allisone['boards'] as $board) {
            $boardObject = $this->syncBoard($board);
            foreach ($board['board_settings'] as $boardSetting) {
                $boardSetting = $this->syncBoardSetting($boardObject, $boardSetting);
            }
        }

        foreach($allisone['users'] as $user) {
            $userObject = $this->syncUser($user);

            foreach($user['monitors'] as $monitor) {
                $board = Board::where('slug', $monitor['board_slug'])->first();
                $monitorObject = $this->syncMonitor($userObject, $monitor, $board);
                foreach($monitor['settings'] as $monitorSetting) {
                    $monitorSetting = $this->syncMonitorSetting($monitorObject, $monitorSetting);
                }
            }
        }
    }

    private function syncUser(
        array $userArray
    ): ?User
    {
        list($name, $email, $password) = $this->arrayToVariables(
            $userArray,
            ['name', 'email', 'password']
        );

        return User::updateOrCreate([
            'email' => $email
        ], [
            'name' => $name,
            'password' => bcrypt($password)
        ]);
    }

    private function syncBoard(
        array $boardArray
    ): ?Board
    {
        list($name, $slug, $description, $active) = $this->arrayToVariables(
            $boardArray,
            ['name', 'slug', 'description', 'active']
        );

        return Board::updateOrCreate([
            'slug' => $slug
        ], [
            'name' => $name,
            'description' => $description,
            'active' => $active
        ]);
    }

    private function syncBoardSetting(
        Board $board,
        mixed $boardSettingArray
    ): ?BoardSetting
    {
        list($key, $description, $required) = $this->arrayToVariables(
            $boardSettingArray,
            ['key', 'description', 'required']
        );

        return $board->boardSettings()->updateOrCreate([
            'key' => $key
        ], [
            'description' => $description,
            'required' => $required
        ]);
    }

    private function syncMonitor(
        User $user,
        array $monitorArray,
        Board $board
    ): ?Monitor
    {
        list($name, $slug, $description, $active) = $this->arrayToVariables(
            $monitorArray,
            ['name', 'slug', 'description', 'active']
        );

        return $user->monitors()->updateOrCreate([
            'slug' => $slug,
            'board_id' => $board->id
        ], [
            'name' => $name,
            'description' => $description,
            'active' => $active,
            'filter' => $monitorArray['filter']
        ]);
    }

    private function syncMonitorSetting(
        Monitor $monitor,
        mixed $monitorSettingArray
    ): ?MonitorSetting
    {
        list($key, $value) = $this->arrayToVariables($monitorSettingArray, ['key', 'value']);

        return $monitor->monitorSettings()->updateOrCreate([
            'key' => $key
        ], [
            'value' => $value
        ]);
    }

    private function arrayToVariables(
        array $serviceArray,
        array $variables
    ): array
    {
        $values = [];
        foreach ($variables as $variable) {
            $values[] = data_get($serviceArray, $variable, null);
        }
        return $values;
    }

}
