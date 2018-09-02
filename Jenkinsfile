def secrets = [
        text : [
            sentry_dsn: 'sentry_dsn',
            sentry_public_dsn: 'sentry_public_dsn'
        ]
    ]

aslApp {
    slack_channel='#infra'
    group='papersail'
    owner='veryveryshort'
    apps=[
        [
            name:'papersail-php',
            branch: 'artestudio',
            env: 'prod',
            deploy: [
                timeoutms: 360000
            ],
            vars: [
		        hc_path: '/ping.html',
			vhost: 'papersail.veryveryshort.com, papersail.trestrescourt.com, papersail.sehrsehrkurz.com'
	        ],
            secrets: secrets
        ]            
    ]
}
