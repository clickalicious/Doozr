<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Doozr - Logging - System
 *
 * System.php - This logging logs all passed content to systems (OS) default
 * log system.
 *
 * PHP versions 5.5
 *
 * LICENSE:
 * Doozr - The lightweight PHP-Framework for high-performance websites
 *
 * Copyright (c) 2005 - 2016, Benjamin Carl - All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * - All advertising materials mentioning features or use of this software
 *   must display the following acknowledgment: This product includes software
 *   developed by Benjamin Carl and other contributors.
 * - Neither the name Benjamin Carl nor the names of other contributors
 *   may be used to endorse or promote products derived from this
 *   software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Please feel free to contact us via e-mail: opensource@clickalicious.de
 *
 * @category   Doozr
 * @package    Doozr_Logging
 * @subpackage Doozr_Logging_System
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2016 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id$
 * @link       http://clickalicious.github.com/Doozr/
 */

require_once DOOZR_DOCUMENT_ROOT . 'Doozr/Logging/Abstract.php';
require_once DOOZR_DOCUMENT_ROOT . 'Doozr/Logging/Interface.php';
require_once DOOZR_DOCUMENT_ROOT . 'Doozr/Logging/Constant.php';

use Psr\Log\LoggerInterface;

/**
 * Doozr - Logging - System
 *
 * This logging logs all passed content to systems (OS) default
 * log system.
 *
 * @category   Doozr
 * @package    Doozr_Logging
 * @subpackage Doozr_Logging_System
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2016 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id$
 * @link       http://clickalicious.github.com/Doozr/
 * @see        Abstract.php, Interface.php
 */
class Doozr_Logging_System extends Doozr_Logging_Abstract
    implements
    Doozr_Logging_Interface,
    LoggerInterface,
    SplObserver
{
    /**
     * Name of this logging
     *
     * @var string
     * @access protected
     */
    protected $name = 'System';

    /**
     * Version of this logging
     *
     * @var string
     * @access protected
     */
    protected $version = '$Id$';

    /**
     * Writes the log-content to systems log.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function output()
    {
        /*
        Constant Description
        LOG_EMERG   system is unusable
        LOG_ALERT   action must be taken immediately
        LOG_CRIT    critical conditions
        LOG_ERR     error conditions
        LOG_WARNING warning conditions
        LOG_NOTICE  normal, but significant, condition
        LOG_INFO    informational message
        LOG_DEBUG   debug-level message
        */

        $flags = LOG_PID;

        // log entries must follow standard UTC timezone settings
        putenv('TZ=UTC');

        // connect to syslog
        #$flags = $flags | LOG_PERROR;
        openlog($_SERVER['REQUEST_URI'], $flags, LOG_DAEMON);

        // first get content to local var
        $content = $this->getContentRaw();

        // iterate log content
        foreach ($content as $logEntry) {
            // convert our type to systems log type
            $type = $this->typeToSystemType($logEntry['type']);

            // log to syslog
            syslog($type, $logEntry['message']);
        }

        // so we can clear the existing log
        $this->clearContent();

        // close connection to syslog
        closelog();
    }

    /*------------------------------------------------------------------------------------------------------------------
    | Fulfill SplObserver
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Update of SplObserver
     *
     * @param SplSubject $subject The subject we work on
     * @param null       $event   The event to process (optional)
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access public
     */
    public function update(SplSubject $subject, $event = null)
    {
        switch ($event) {
            case 'log':
                /* @var Doozr_Logging $subject */
                $logs = $subject->getCollectionRaw();

                foreach ($logs as $log) {
                    $this->log(
                        $log['type'],
                        $log['message'],
                        unserialize($log['context']),
                        $log['time'],
                        $log['fingerprint'],
                        $log['separator']
                    );
                }
                break;
        }
    }

    /*------------------------------------------------------------------------------------------------------------------
    | Internal Tools & Helper
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * This method is intend to add the defined line-separator to log-content.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return bool TRUE on success, otherwise FALSE
     * @access protected
     */
    protected function separate()
    {
        // do nothing to seperate in system logging
        return true;
    }

    /**
     * Converts the passed type to systems log type and returns
     * this type.
     *
     * @param string $type The type to convert to systems log type.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return integer The systems log type
     * @access protected
     */
    protected function typeToSystemType($type)
    {
        /*
        'emergency' => 7,
        'alert'     => 6,
        'critical'  => 5,
        'error'     => 4,
        'warning'   => 3,
        'notice'    => 2,
        'info'      => 1,
        'debug'     => 0,
         */
        switch ($type) {
            case 'emergency':
                $type = LOG_EMERG;
                break;
            case 'alert':
                $type = LOG_ALERT;
                break;
            case 'critical':
                $type = LOG_CRIT;
                break;
            case 'error':
                $type = LOG_ERR;
                break;
            case 'warning':
                $type = LOG_WARNING;
                break;
            case 'notice':
                $type = LOG_NOTICE;
                break;
            case 'info':
                $type = LOG_INFO;
                break;
            default:
            case 'debug':
                $type = LOG_DEBUG;
                break;
        }

        return $type;
    }

    /*-----------------------------------------------------------------------------------------------------------------+
    | Fulfill Abstract Requirements
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Dispatches a new route to this logging (e.g. for use as new filename).
     *
     * @param string $name The name of the route to dispatch
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access public
     */
    public function route($name)
    {
        /**
         * This logging does not need to be re-routed
         */
    }
}
