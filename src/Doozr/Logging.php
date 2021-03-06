<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Doozr - Logging.
 *
 * Logging.php - This logging is the composite for accessing the logging-subsystem of Doozr.
 * This logging is the main entry point for all log-content. This logging takes any log and
 * dispatch this to the attached loggers.
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
 *
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2016 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 * @version    Git: $Id$
 *
 * @link       http://clickalicious.github.com/Doozr/
 */
require_once DOOZR_DOCUMENT_ROOT.'Doozr/Logging/Abstract.php';
require_once DOOZR_DOCUMENT_ROOT.'Doozr/Logging/Interface.php';
require_once DOOZR_DOCUMENT_ROOT.'Doozr/Logging/Constant.php';

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Doozr - Logging.
 *
 * This logging is the composite for accessing the logging-subsystem of Doozr.
 * This logging is the main entry point for all log-content. This logging takes any log and
 * dispatch this to the attached loggers.
 *
 * @category   Doozr
 *
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2016 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 * @version    Git: $Id$
 *
 * @link       http://clickalicious.github.com/Doozr/
 * @final
 */
final class Doozr_Logging extends Doozr_Logging_Abstract
    implements
    Doozr_Logging_Interface,
    SplSubject,
    ArrayAccess,
    Iterator,
    Countable,
    LoggerAwareInterface
{
    /**
     * The observer storage.
     *
     * @var SplObjectStorage
     */
    protected $observer;

    /**
     * The default log level from configuration (once set).
     *
     * @var int
     */
    protected $defaultLoglevel;

    /**
     * Name of this logging.
     *
     * @var string
     */
    protected $name = 'Composite';

    /**
     * Version of this logging.
     *
     * @var string
     */
    protected $version = 'Git: $Id$';

    /**
     * The position of the iterator for iterating
     * elements.
     *
     * @var int
     */
    protected $position = 0;

    /*------------------------------------------------------------------------------------------------------------------
    | Init
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Constructor.
     *
     * @param Doozr_Datetime_Service $datetime    Instance of Datetime-Service for date-operations
     * @param int|null               $level       The logging level | if not passed the max is set
     * @param string|null            $fingerprint The fingerprint of the current client|will be
     *                                            generated if not passed
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return \Doozr_Logging
     */
    public function __construct(Doozr_Datetime_Service $datetime = null, $level = null, $fingerprint = null)
    {
        // Instantiate the SplObjectStorage
        $this->observer = new SplObjectStorage();

        // and then call the original constructor
        parent::__construct($datetime, $level, $fingerprint);
    }

    /*------------------------------------------------------------------------------------------------------------------
    | PUBLIC API
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Dispatches a passed message and all arguments to attached loggers.
     *
     * @param string $type        The level/type of the log entry
     * @param string $message     The message to log
     * @param array  $context     The context with variables used for interpolation
     * @param string $time        The time to use for logging
     * @param string $fingerprint The fingerprint to use/set
     * @param string $separator   The separator to use/set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return bool TRUE on success, otherwise FALSE
     */
    public function log(
              $type,
              $message,
        array $context = [],
              $time = null,
              $fingerprint = null,
              $separator = null
    ) {
        // call parents log just as normal => so content, raw ... gets filled
        parent::log($type, $message, $context, $time, $fingerprint, $separator);

        // log date time
        $time = ($time !== null) ?
            $time :
            $this->date.' ['.$this->dateTime->getMicrotimeDiff($_SERVER['REQUEST_TIME']).']';

        // Store message in archive for e.g. debug bar and similar outputs
        $this->archive(
            sha1($message.$type.$fingerprint),
            [
                'type'        => $type,
                'message'     => $message,
                'context'     => $context,
                'time'        => $time,
                'fingerprint' => $fingerprint,
                'separator'   => $separator,
            ]
        );

        // and now the tricky hook -> notify all observers about the log-event
        $this->notify('log');

        // return always success
        return true;
    }

    /**
     * Removes all attached loggers. Optionally it removes
     * all contents as well.
     *
     * @param bool $clearContents TRUE to remove all content from logging,
     *                            FALSE to keep it
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function detachAll($clearContents = false)
    {
        $this->observer = new SplObjectStorage();

        if (true === $clearContents) {
            $this->clear();
        }
    }

    /*------------------------------------------------------------------------------------------------------------------
    | Getter & Setter
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Returns a logging by its name.
     *
     * @param string $name The name of the logging
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return Doozr_Logging_Interface|null The logging if exist, otherwise NULL
     */
    public function getLogger($name)
    {
        foreach ($this->observer as $logger) {
            if (strtolower($logger->getName()) === strtolower($name)) {
                return $logger;
            }
        }

        return;
    }

    /**
     * This method is intend to set the default log-level.
     *
     * @param int $level The log-level to set as standard
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function setDefaultLoglevel($level)
    {
        $this->defaultLoglevel = $level;
    }

    /**
     * This method is intend to return the default log-level.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return int The default log-level
     */
    public function getDefaultLoglevel()
    {
        return $this->defaultLoglevel;
    }

    /*------------------------------------------------------------------------------------------------------------------
    | Fulfill LoggerAwareInterface
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Setter for logging.
     *
     * @param LoggerInterface $logger The logging to attach
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @throws Doozr_Logging_Exception
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        if (!$logger instanceof SplObserver) {
            throw new Doozr_Logging_Exception(
                sprintf('Please implement SplObserver before trying to attach your logging.')
            );
        }

        $this->attach($logger);

        return null;
    }

    /*------------------------------------------------------------------------------------------------------------------
    | Fulfill SplSubject
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Attaches an observer instance.
     *
     * @param SplObserver $observer The observer to attach
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function attach(SplObserver $observer)
    {
        $this->observer->attach($observer);
    }

    /**
     * Detaches an observer instance.
     *
     * @param SplObserver $observer The observer to detach
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function detach(SplObserver $observer)
    {
        $this->observer->detach($observer);
    }

    /**
     * Notifies the attached observers about changes.
     *
     * @param string $event The event to send with notify
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function notify($event = null)
    {
        /* @var Doozr_Base_Crud_Interface $observer */
        foreach ($this->observer as $observer) {
            $observer->update($this, $event);
        }

        $this->clear();
    }

    /*------------------------------------------------------------------------------------------------------------------
    | Internal Tools & Helpers
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Suppress the output.
     * This logging implementation is Composite and does not echo any
     * log-content it just dispatch the stuff to its attached observers.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return bool TRUE as dummy return value
     */
    protected function output()
    {
        // return true -> cause not needed for collecting logging
        return true;
    }

    /*-----------------------------------------------------------------------------------------------------------------+
    | Fulfill ArrayAccess
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Returns the TRUE if the passed offset exists otherwise FALSE.
     *
     * @param mixed $offset The offset to check
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return bool The result of the operation
     */
    public function offsetExists($offset)
    {
        foreach ($this->observer as $observer) {
            if (strtolower($observer->getName()) === strtolower($offset)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the value for the passed offset.
     *
     * @param mixed $offset The offset to return value for
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return mixed The result of the operation
     */
    public function offsetGet($offset)
    {
        foreach ($this->observer as $observer) {
            if (strtolower($observer->getName()) === strtolower($offset)) {
                return $observer;
            }
        }

        return;
    }

    /**
     * Sets the value for the passed offset.
     *
     * @param int   $offset The offset to set value for
     * @param mixed $value  The value to write
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return mixed The result of the operation
     */
    public function offsetSet($offset, $value)
    {
        foreach ($this->observer as $observer) {
            if (strtolower($observer->getName()) === strtolower($offset)) {
                $this->observer->offsetSet($value);
            }
        }
    }

    /**
     * Unsets an offset.
     *
     * @param mixed $offset The offset to unset
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return mixed The result of the operation
     */
    public function offsetUnset($offset)
    {
        foreach ($this->observer as $observer) {
            if (strtolower($observer->getName()) === strtolower($offset)) {
                $this->observer->detach($observer);
            }
        }

        return;
    }

    /*-----------------------------------------------------------------------------------------------------------------+
    | Fulfill Iterator
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Rewinds the position to 0.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return mixed The result of the operation
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Checks if current position is still valid.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return bool The result of the operation
     */
    public function valid()
    {
        return $this->position < count($this->observer);
    }

    /**
     * Returns the key for the current position.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return int The result of the operation
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Returns the current element.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return mixed The result of the operation
     */
    public function current()
    {
        $offset = 0;

        foreach ($this->observer as $observer) {
            if ($offset === $this->position) {
                return $observer;
            }
            ++$offset;
        }
    }

    /**
     * Goes to next element.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return mixed The result of the operation
     */
    public function next()
    {
        ++$this->position;
    }

    /*-----------------------------------------------------------------------------------------------------------------+
    | Fulfill Countable
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Returns the count of elements in registry.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return int The result of the operation
     */
    public function count()
    {
        return count($this->observer);
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
     *
     * @return bool TRUE on success, otherwise FALSE
     */
    public function route($name)
    {
        /*
         * This logging does not need to be re-routed
         */
        return true;
    }
}
