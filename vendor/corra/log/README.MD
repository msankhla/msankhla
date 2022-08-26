Documentation link: https://corratech.jira.com/wiki/spaces/EKC/pages/801964223/Corra+Log+Module+-+M2

This is a temporary fix to resolve performanc issues.

after installing this module add following entries in app/etc/env.php file to enable this module.


'corra_log' => [
        'general' => [
            'enable_corra_logging' => 1
        ],
        'graylog' => [
            'enable_corra_logging' => 0
        ]
		
		

