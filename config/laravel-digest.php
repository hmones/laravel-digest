<?php

return [
    /*
     * Method option specifies the technique used in sending the digest mail. Two options are available here, send and queue.
     * Make sure if you choose queue that your mailable is queueable for the best performance.
     * The default value is queue
     */
    'method' => 'queue',
    /*
     * Duration option specifies whether you want to send the digest mail every specific period.
     * The option can be enabled, disabled by setting enabled flag.
     * The frequency setting can be configured via the type parameter which takes one of three values (daily, weekly, monthly)
     * The time of sending the email can be set via the time parameter,
     * The day in which the email is sent can be specified by the day parameter, please note that
     * for monthly mails the day is the day of the month and for weekly mails the day is the day of the week, where sunday is 1
     */
    'frequency' => [
        'enabled'   => true,
        'daily' => [
            'time'      => '00:00',
        ],
        'weekly' => [
            'time'      => '00:00',
            'day'       => 1,
        ],
        'monthly' => [
            'time'      => '00:00',
            'day'       => 1,
        ],
        /*
         * You can define here multiple custom scenarios under different names, that fits your needs,
         * each custom scenario should have a cron expression that specifies the custom schedule that fits your needs.
         * A cron expression takes the following format (default is yearly):
         * *(minute) *(hour) *(day of month) *(month) *(day of week)
         */
        'custom' => '0 0 1 1 *',
    ],
    /*
     * Amount option specifies whether you want to send the gigest email every specific number of emails that accumulate for the digest
     * This can be optimum for frequently recurring issues on your app that you would like to be informed about after certain threshold
     */
    'amount' => [
        'enabled'   => true,
        'threshold' => 10,
    ],
];
