<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Doozr - Di - Exporter - Json.
 *
 * Json.php - Di exporter (JSON).
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
 * @link       https://github.com/clickalicious/Di
 */
require_once DOOZR_DOCUMENT_ROOT.'Doozr/Di/Exporter/Abstract.php';
require_once DOOZR_DOCUMENT_ROOT.'Doozr/Di/Exporter/Interface.php';
require_once DOOZR_DOCUMENT_ROOT.'Doozr/Di/Dependency.php';
require_once DOOZR_DOCUMENT_ROOT.'Doozr/Di/Collection.php';
require_once DOOZR_DOCUMENT_ROOT.'Doozr/Di/Object/Freezer.php';

/**
 * Doozr - Di - Exporter - Json.
 *
 * Di exporter (JSON).
 *
 * @category   Doozr
 *
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2016 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 * @link       https://github.com/clickalicious/Di
 */
class Doozr_Di_Exporter_Json extends Doozr_Di_Exporter_Abstract
    implements
    Doozr_Di_Exporter_Interface
{
    /*------------------------------------------------------------------------------------------------------------------
    | PUBLIC API
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Exports current content of Doozr_Di_Collection ($this->collection) to a JSON-File.
     *
     * This method is intend to write current content of Doozr_Di_Collection ($this->collection) to a JSON-File.
     *
     * @param bool $exportInstances TRUE to export instances as well, otherwise FALSE to do not
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return bool TRUE on success, otherwise FALSE
     *
     * @throws Doozr_Di_Exception
     */
    public function export($exportInstances = true)
    {
        // check for collection
        if (!$this->collection) {
            throw new Doozr_Di_Exception(
                'Could not import map. No collection set. Please set a collection first.'
            );
        }

        // check for input
        if (!$this->output) {
            throw new Doozr_Di_Exception(
                'Could not export map. No output file set. Please set output first.'
            );
        }

        // check if input directory exists and if it is writable
        if (!is_dir(dirname($this->output)) || (!is_writable(dirname($this->output)))) {
            throw new Doozr_Di_Exception(
                sprintf(
                    'Could not export map. Output directory "%s" does not exist or isn\'t writable.',
                    Dirname($this->output)
                )
            );
        }

        /*
        "Foo": {
            "arguments": ["I R Baboon!"],
            "dependencies": [
                {
                    "id": "Database1",
                    "className": "Database",
                    "arguments": ["foo", "bar", "baz"],
                    "instance": null,
                    "configuration": {
                        "type": "constructor"
                    }
                },
                {
                    "id": "Logger1",
                    "className": "Logger",
                    "instance": null,
                    "configuration": {
                        "type": "method",
                        "value": "setLogging"
                    }
                }
            ]
        }
        */

        // get instance of the freezer
        $freezer = new Object_Freezer();

        // the collection for export in correct JSON structure
        $collection = [];

        // iterate over collection
        foreach ($this->collection as $className => $dependencies) {

            // collect dependencies for $className in an array
            $collection[$className] = new stdClass();

            // check for arguments
            ($this->collection->getArguments($className)) ?
                $collection[$className]->arguments = $this->collection->getArguments($className) :
                null;

            // check for custom arguments
            ($this->collection->getConstructor($className)) ?
                $collection[$className]->constructor = $this->collection->getConstructor($className) :
                null;

            // iterate over existing dependencies, encrypt to JSON structure and store temporary in $collection[]
            foreach ($dependencies as $count => $dependency) {
                /* @var $dependency Doozr_Di_Dependency */

                // temp object for storage
                $tmp = new stdClass();

                // the target
                $tmp->target = $dependency->getTarget();

                // the className
                $tmp->className = $dependency->getClassName();

                // the arguments
                if ($dependency->getArguments()) {
                    $tmp->arguments = $dependency->getArguments();
                }

                // the instance
                if ($exportInstances === true) {
                    if (is_object($dependency->getInstance())) {
                        $tmp->instance = serialize($freezer->freeze($dependency->getInstance()));
                    } else {
                        $tmp->instance = $dependency->getInstance();
                    }
                } else {
                    $tmp->instance = null;
                }

                // the configuration
                $tmp->config = $dependency->getConfiguration();

                // store created object to $collection
                $collection[$className]->dependencies[] = $tmp;
            }
        }

        // create tmp object for JSON export
        $output = new stdClass();

        // set collection as output for our map
        $output->map = [
            $collection,
        ];

        // write content to file
        $this->writeFile(
            $this->output, json_encode($output)
        );

        // success
        return true;
    }

    /**
     * Imports content to store as collection for later export.
     *
     * This method is intend to set the collection used for export.
     *
     * @param Doozr_Di_Collection $collection The collection instance of Doozr_Di_Collection to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function import(Doozr_Di_Collection $collection)
    {
        $this->collection = $collection;
    }
}
