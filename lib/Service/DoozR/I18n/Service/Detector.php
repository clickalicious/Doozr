<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * DoozR - I18n - Service
 *
 * Detector.php - Locale detection part of the I18n service.
 *
 * PHP versions 5.4
 *
 * LICENSE:
 * DoozR - The lightweight PHP-Framework for high-performance websites
 *
 * Copyright (c) 2005 - 2015, Benjamin Carl - All rights reserved.
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
 *   must display the following acknowledgement: This product includes software
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
 * @category   DoozR
 * @package    DoozR_Service
 * @subpackage DoozR_Service_I18n
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2015 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id$
 * @link       http://clickalicious.github.com/DoozR/
 */

require_once DOOZR_DOCUMENT_ROOT . 'DoozR/Base/Class/Singleton.php';

/**
 * DoozR - I18n - Service
 *
 * Locale detection part of the I18n service.
 *
 * @category   DoozR
 * @package    DoozR_Service
 * @subpackage DoozR_Service_I18n
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2015 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id$
 * @link       http://clickalicious.github.com/DoozR/
 */
class DoozR_I18n_Service_Detector extends DoozR_Base_Class_Singleton
{
    /**
     * Array containing the "defaults" for fallback solutions
     *
     * @var array
     * @access private
     * @static
     */
    private static $_defaults;

    /**
     * Store(s) available for storing preferences
     *
     * @var array
     * @access private
     * @static
     */
    private static $_stores;

    /**
     * Identifier used to identify I18n prefered data in store(s)
     *
     * @var string
     * @access private
     * @static
     */
    private static $_identifier;

    /**
     * Array containing all available locales on the system
     *
     * @var string[]
     * @access private
     * @static
     */
    private static $_availableLocales;

    /**
     * Instance of session service
     *
     * @var object
     * @access private
     * @static
     */
    private static $_session;

    /**
     * Status of initialization
     *
     * @var bool
     * @access private
     * @static
     */
    private static $_initialized = false;

    /**
     * Current active and prefered locale
     *
     * @var string
     * @access private
     */
    private $_locale;

    /**
     * Current active and preferred weight
     *
     * @var double
     * @access private
     */
    private $_weight;

    /**
     * Current active and preferred language
     *
     * @var string
     * @access private
     */
    private $_language;

    /**
     * Current active and preferred country(-code)
     *
     * @var string
     * @access private
     */
    private $_country;

    /**
     * Array containing all detected locales
     *
     * @var array
     * @access private
     */
    private $_detectedLocales;

    /**
     * Array containing all detected languages
     *
     * @var array
     * @access private
     */
    private $_detectedLanguages;

    /**
     * Array containing all detected countries
     *
     * @var array
     * @access private
     */
    private $_detectedCountries;

    /**
     * The lifetime for stored preferences
     *
     * @var int
     * @access private
     */
    private static $_preferenceLifetime = 7776000;

    /**
     * TRUE if any new value was detected, otherwise
     * FALSE
     *
     * @var bool
     * @access private
     */
    private $_touched = false;

    /**
     * Instance of Doozr_Registry
     *
     * @var DoozR_Registry_Interface
     * @access protected
     */
    protected static $registry;

    /**
     * Runtime environment (CLI || WEB || HTTPD)
     * To know if cookie and session is accessible
     *
     * @var string
     * @access protected
     */
    protected static $runtimeEnvironment;


    /**
     * This method is intend to act as constructor.
     *
     * @param DoozR_Config_Interface   $config   Instance of DoozR_Config_Ini containing the I18n-config
     * @param DoozR_Registry_Interface $registry Instance of DoozR_Registry
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return \DoozR_I18n_Service_Detector Instance of this class
     * @access protected
     */
    protected function __construct(DoozR_Config_Interface $config, DoozR_Registry_Interface $registry)
    {
        // Store registry
        self::$registry           = $registry;
        self::$runtimeEnvironment = self::$registry->getRequest()->getRuntimeEnvironment();

        // locale defaults
        self::$_defaults = array(
            'locale'   => $config->i18n->defaults->locale,
            'language' => $config->i18n->defaults->language,
            'country'  => $config->i18n->defaults->country,
            'weight'   => $config->i18n->defaults->weight
        );

        // a collection of locales available
        self::$_availableLocales = (array)$config->i18n->defaults->available;

        // get "prefered-locale"-stores in correct order
        self::$_stores = $config->i18n->user->stores;

        // get lifetime for stored preference data
        self::$_preferenceLifetime = $config->i18n->user->lifetime;

        // the identifier for stores
        self::$_identifier = $config->i18n->user->identifier;
    }

    /*------------------------------------------------------------------------------------------------------------------
    | BEGIN PUBLIC API
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Returns the whole collection of detected values
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array The collected values
     * @access public
     */
    public function get()
    {
        return array(
            'locale' => $this->getLocale(),
        );
    }

    /**
     * This method is intend to return the current active locale in consideration of order.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The current active locale like "de" or "at"
     * @access public
     */
    public function getLocale()
    {
        // get the stored locale
        return $this->_locale;
    }

    /**
     * Returns the locale as doubled locale value like "de" will be "de-de" to
     * equalite the format with values like "de-at", "en-gb" ...
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The formatted locale e.g. "de-de" ...
     * @access public
     */
    public function getDoubledLocale()
    {
        $locale = $this->getLocale();

        if (stristr($locale, '-') === false) {
            $locale = $locale . '-' . $locale;
        }

        return $locale;
    }

    /**
     * This method is intend to return the weight of the current active locale
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The current active locale like "de" or "at"
     * @access public
     */
    public function getWeight()
    {
        // Get the stored weight of locale
        return $this->_weight;
    }

    /**
     * This method is intend to return all detected locales
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array All detected locales
     * @access public
     */
    public function getLocales()
    {
        // Get all stored locales
        return $this->_detectedLocales;
    }

    /**
     * This method is intend to return the current active country
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The current active country like "de" or "at"
     * @access public
     */
    public function getCountry()
    {
        // get the stored country
        return $this->_country;
    }

    /**
     * This method is intend to return all detected countries
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array All detected countries
     * @access public
     */
    public function getCountries()
    {
        // get all stored locales
        return $this->_detectedCountries;
    }

    /**
     * This method is intend to return the current active language
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The current active language like "de" or "at"
     * @access public
     */
    public function getLanguage()
    {
        // get the stored language
        return $this->_language;
    }

    /**
     * This method is intend to return all detected languages
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array All detected languages
     * @access public
     */
    public function getLanguages()
    {
        // get all stored locales
        return $this->_detectedLanguages;
    }

    /**
     * This method is intend to return the complete set of current active locale, language + country.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array The complete set of current active locale-settings
     * @access public
     */
    public function getLocalePreferences()
    {
        return array(
            'locale'   => $this->_locale,
            'language' => $this->_language,
            'country'  => $this->_country
        );
    }

    /**
     * This method is intend to check if all requirements are fulfilled.
     *
     * @param string $code de, de-AT, en-us ...
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean TRUE if valid, otherwise FALSE
     * @access public
     */
    public function isValidLocaleCode($code = '')
    {
        return (preg_match('(^([a-zA-Z]{2})((_|-)[a-zA-Z]{2})?$)', $code) > 0) ? true : false;
    }

    /**
     * This method is intend to detect the user prefered locale.
     *
     * @param bool $lookupAlternative TRUE to try to find a matching locale, FALSE to use systems default as fallback
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return DoozR_I18n_Service_Detector Instance for chaining
     * @access public
     */
    public function detect($lookupAlternative = true)
    {
        if (!self::$_initialized) {
            // finally init
            self::$_initialized = $this->init($lookupAlternative);
        }

        return $this;
    }

    /**
     * Overrides locale config and stores it to configured stores.
     *
     * @param array $preferences The preferences to store
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return DoozR_I18n_Service_Detector Instance for chaining
     * @access public
     */
    public function override(array $preferences)
    {

        // store retrieved data in class and afterwards in store(s)
        $this->_locale   = $preferences['locale'];
        $this->_weight   = $preferences['weight'];
        $this->_language = $preferences['language'];
        $this->_country  = $preferences['country'];

        $this->_writePreferences($preferences);

        return $this;
    }

    /*------------------------------------------------------------------------------------------------------------------
    | BEGIN TOOLS + HELPER
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * This method is intend to initialize and start the detection.
     *
     * @param bool $lookupAlternative TRUE to try to find a matching locale, FALSE to use systems default as fallback
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean TRUE if detection was successful
     * @access protected
     */
    protected function init($lookupAlternative)
    {
        // 1st try to retrieve previously stored pereferences from session (fastet store) and cookie
        $userPreferences = $this->readPreferences();

        // retrieving of stored preferences failed
        if ($userPreferences === null) {
            // now try to detect
            $userPreferences = $this->_detectPreferences();

            // check if retrieved locale exists
            if ($userPreferences === false || !in_array($userPreferences['locale'], self::$_availableLocales)) {

                // look in the list of retrieved locales for the next matching one (alternative = true)
                if ($lookupAlternative) {
                    // reset the one found
                    $userPreferences = null;

                    // get count of found locales
                    $countDetectedLocales = count($this->_detectedLocales);

                    // iterate over all detected locales to find the first matching one
                    for ($i = 1; $i < $countDetectedLocales; ++$i) {
                        // check if we got a matching locale
                        if (in_array($this->_detectedLocales[$i]['locale'], self::$_availableLocales)) {
                            $userPreferences['locale']   = $this->_detectedLocales[$i]['locale'];
                            $userPreferences['weight']   = $this->_detectedLocales[$i]['weight'];
                            $userPreferences['language'] = $this->_detectedLanguages[$i];
                            $userPreferences['country']  = $this->_detectedCountries[$i];

                            // leave loop
                            break;
                        }
                    }
                }

                // if we should'nt lookup an alternative OR no locale was detected -> use systems default
                if (!$lookupAlternative || $userPreferences === null) {
                    // or if we use the systems default locale if auto-detect fails
                    $userPreferences['locale']   = self::$_defaults['locale'];
                    $userPreferences['weight']   = self::$_defaults['weight'];
                    $userPreferences['language'] = self::$_defaults['language'];
                    $userPreferences['country']  = self::$_defaults['country'];
                }
            }

            // we did change something
            $this->_touched = true;
        }

        // store retrieved data in class and afterwards in store(s)
        $this->_locale   = $userPreferences['locale'];
        $this->_weight   = $userPreferences['weight'];
        $this->_language = $userPreferences['language'];
        $this->_country  = $userPreferences['country'];

        // finally store in defines stores
        if ($this->_touched) {
            $this->_writePreferences($userPreferences);
        }

        // init successfully done (means locale, language and country are set to an existing locale)
        return true;
    }

    /**
     * This method is intend to write the given preferences to all configured stores.
     *
     * @param array $preferences The preferences to store
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean TRUE if storing was successful, otherwise FALSE
     * @access private
     */
    private function _writePreferences(array $preferences)
    {
        // assume result true
        $result = true;

        if (self::$runtimeEnvironment !== DoozR_Request_State::RUNTIME_ENVIRONMENT_CLI) {

            // iterate over stores and try to reconstruct the previously stored preferences
            foreach (self::$_stores as $store) {

                // construct method-name for current store
                $method = 'write'.ucfirst($store);

                // try to get preferences from store
                $result = $result && $this->{$method}($preferences);
            }
        }

        // successfully retrieved
        return $result;
    }

    /**
     * This method is intend to control the detection of the users locale preferences.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array An array containing the prefered "locale" + "weight", "language" and "country"
     * @access private
     */
    private function _detectPreferences()
    {
        // assume empty result
        $detectedPreferences = false;

        // prevent access to HEADER and IP in CLI does not make sense
        if (self::$runtimeEnvironment !== DoozR_Request_State::RUNTIME_ENVIRONMENT_CLI) {

            // try to detect locale by user-agents header
            $detectedPreferences = $this->detectByRequestHeader();

            // FALLBACK: try to detect by user's ip/dns-hostname
            if (!$detectedPreferences) {
                // FALLBACK: try to detect by user's ip/dns-hostname
                $detectedPreferences = $this->detectByUserIp();
            }
        }

        // and finally store the detected results
        return $detectedPreferences;
    }

    /**
     * This method is intend to return the user's prefered, previously stored, locale from store.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return mixed ARRAY containing the prefered-config, otherwise NULL
     * @access protected
     */
    protected function readPreferences()
    {
        // assume empty user-preferences
        $storedPreferences = null;

        if (self::$runtimeEnvironment !== DoozR_Request_State::RUNTIME_ENVIRONMENT_CLI) {

            // iterate over stores and try to reconstruct the previously stored preferences
            foreach (self::$_stores as $store) {
                // construct method-name for current store
                $method = 'read'.ucfirst($store);

                // try to get preferences from store
                $storedPreferences = $this->{$method}();

                // if result was retrieved successfully we can
                if ($storedPreferences) {
                    break;
                }
            }
        }

        // successfully retrieved
        return $storedPreferences;
    }

    /**
     * Returns an instance of the Session-Service
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return DoozR_Session_Service Instance of service Session
     * @access protected
     */
    protected function getSession()
    {
        if (!self::$_session) {
            self::$_session = DoozR_Loader_Serviceloader::load('session');
        }

        return self::$_session;
    }

    /**
     * This method is intend to detect the available locales by request-header.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return bool|array FALSE on error, otherwise ARRAY containing the preferred locale(s)
     * @access protected
     */
    protected function detectByRequestHeader()
    {
        // direct stop of processing if needed header not set
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return false;
        }

        // get header accept-language
        //$acceptedLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        // TODO: remove
        $acceptedLanguages = explode(',', 'de-de,de;q=0.8,en;q=0.3,en-us;q=0.5');

        // valid result?
        if (count($acceptedLanguages) < 1) {
            return false;
        }

        // iterate over raw header-entries and extract
        foreach ($acceptedLanguages as $acceptedLanguage) {
            // prepare by splitting
            $parts = explode(';', $acceptedLanguage);

            // locale preset
            $locale = strtolower(trim($parts[0]));
            $weight = 1;

            // check if q is set
            if (count($parts) > 1) {
                $weight = trim(str_replace('q=', '', $parts[1]));
            }

            // if locale is valid add it
            if ($this->isValidLocaleCode($locale) && $weight >= 0) {
                $this->addLocale($locale, $weight);
            }
        }

        // Now sort by it's weight!
        $this->_detectedLocales = $this->sortByWeight($this->_detectedLocales);

        // iterate over sorted result and retrieve language and country in correct order
        foreach ($this->_detectedLocales as $localeSet) {
            $parts = explode('-', $localeSet['locale']);
            $parts[1] = (isset($parts[1])) ? $parts[1] : $parts[0];

            // get language and country validated
            $language = ($this->isValidLocaleCode($parts[0])) ? $parts[0] : null;
            $country = ($this->isValidLocaleCode($parts[1])) ? $parts[1] : null;

            // store
            $this->_detectedLanguages[] = $language;
            $this->_detectedCountries[] = $country;
        }

        // Success - return the preferred locale-set
        return array(
            'locale'   => $this->_detectedLocales[0]['locale'],
            'weight'   => $this->_detectedLocales[0]['weight'],
            'language' => $this->_detectedLanguages[0],
            'country'  => $this->_detectedCountries[0]
        );
    }

    /**
     * This method is intend to detect available locale(s) by requesting hostname/client-ip|domain.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean TRUE on success, otherwise FALSE
     * @access protected
     */
    protected function detectByUserIp()
    {
        // get ip
        $ip = $_SERVER['REMOTE_ADDR'];

        // hostname (dialin/dialup hostname e.g. PH-1411J-uBR10k-02-Te-1-2-0.bilk.unity-media.net)
        $host = gethostbyaddr($ip);

        // get country by hostname
        $country = $this->translateDomainToCountrycode($host);

        // if not retrieved a valid result => we check the server's domain!
        if (!$country) {
            $country = $this->translateDomainToCountrycode($_SERVER['SERVER_NAME']);
        }

        // if still no result => assume operation failed!
        if (!$country) {
            return false;
        }

        // store country + make unique
        if (!in_array($country, $this->_detectedCountries)) {
            $this->_detectedCountries[] = $country;
        }

        // success
        return true;
    }

    /**
     * This method is intend to sort the detected locales by it's weight
     *
     * @param array $locales The locales array to sort by weight
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return array By weight sorted elements
     * @access private
     */
    protected function sortByWeight(array $locales)
    {
        // new index
        $index = array();

        // for those who are interested in (int) or intval() whats faster:
        // http://www.entwicklerblog.net/php/php-variable-in-integer-verwandeln-intfoo-oder-intvalfoo/
        foreach ($locales as $localeSet) {
            $index[$localeSet['locale']] = (int)$localeSet['weight'];
        }

        // make values the keys
        $index = array_flip($index);

        // sort by key
        krsort($index);

        // rebuild
        $locales = array();

        foreach ($index as $weight => $locale) {
            $locales[] = array(
                'locale' => $locale,
                'weight' => ($weight/10) // restore the correct weight here -> after sorting
            );
        }

        // return new constructed
        return $locales;
    }

    /**
     * adds a locale (and its language + country) to the list of available locales
     *
     * @param string $locale The locale to add
     * @param double $weight The weight as double (e.g. 0.8 [q=0.8])
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return object Instance of this class
     * @access protected
     */
    protected function addLocale($locale, $weight = 0)
    {
        // doing this we prevent array_flip from throwing error on double/float-values!
        $weight *= 10;

        // insert the locale
        $this->_detectedLocales[] = array(
            'locale' => $locale,
            'weight' => $weight
        );
    }

    /**
     * This method is intend to translate a given domain (like "de" or "com") to countrycode.
     *
     * @param string $domain The domain to retrieve countrycode from
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return mixed STRING countrycode if translation successful, otherwise FALSE
     * @access protected
     */
    protected function translateDomainToCountrycode($domain = '')
    {
        // check for dotted hostname (domain)
        if (strrpos($domain, '.') < 1) {
            return false;
        }

        // split by dot
        $parts = explode('.', $domain);

        // get domain (last element of previously explode op)
        $domain = array_pop($parts);

        // top level domains (com, org, gov, aero, info) or eu-domain are all english
        if (strlen($domain) > 2 || $domain === 'eu') {
            // assume all domain with length > 2 are english
            return 'en';
        } elseif (strlen($domain) == 2) {
            // country domains
            return $domain;
        }

        // no success
        return false;
    }

    /**
     * This method is intend to write preferences to session.
     *
     * @param array $preferences The preferences to store in session
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean TRUE on success, otherwise FALSE
     * @access protected
     */
    protected function writeSession(array $preferences)
    {
        // store preferences in session and return result
        return $this->getSession()->set(self::$_identifier, $preferences);
    }

    /**
     * This method is intend to read a previous stored locale-config from session.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return mixed ARRAY with locale-settings if previously stored in session, otherwise NULL
     * @access protected
     */
    protected function readSession()
    {
        // assume empty cookie / no stored config
        try {
            $storedSettings = $this->getSession()->get(self::$_identifier);

        } catch (DoozR_Session_Service_Exception $e) {
            $storedSettings = null;
        }


        // check the result for validity
        if (!$storedSettings || !$this->isValidLocaleCode($storedSettings['locale'])) {
            return null;
        }

        // return result
        return $storedSettings;
    }

    /**
     * This method is intend to write preferences to a cookie on user's client.
     *
     * @param array $preferences The preferences to store in session
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean TRUE on success, otherwise FALSE
     * @access protected
     */
    protected function writeCookie(array $preferences)
    {
        // combine data
        $data     = implode(',', $preferences);
        $lifetime = time() + self::$_preferenceLifetime;
        $path     = '/';
        $server   = explode('.', $_SERVER['SERVER_NAME']);
        $domain   = '';

        for ($i = 2; $i > 0; --$i) {
            $domain = '.'.$server[$i].$domain;
        }

        // store preferences in cookie and return result
        return setcookie(self::$_identifier, $data, $lifetime, $path, $domain);
    }

    /**
     * This method is intend to read a cookie with a previous stored locale-config (state).
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return boolean TRUE on success, otherwise FALSE
     * @access protected
     */
    protected function readCookie()
    {
        // check if is set
        if (isset($_COOKIE[self::$_identifier])) {
            $storedSettings = explode(',', $_COOKIE[self::$_identifier]);

            // locale
            if ($this->isValidLocaleCode($storedSettings[0])
                && $this->isValidLocaleCode($storedSettings[2])
                && $this->isValidLocaleCode($storedSettings[3])
            ) {
                $locale = array(
                    'locale'   => $storedSettings[0],
                    'weight'   => (double)$storedSettings[1],
                    'language' => $storedSettings[2],
                    'country'  => $storedSettings[3]
                );
            } else {
                $locale = null;
            }
        }

        // return result
        return (isset($locale)) ? $locale : null;
    }
}