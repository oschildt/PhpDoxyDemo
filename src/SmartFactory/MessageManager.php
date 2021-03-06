<?php
/**
 * This file contains the implementation of the interface IMessageManager
 * in the class MessageManager for working with messages - errors, warnings etc.
 *
 * @package System
 *
 * @author Oleg Schildt
 */

namespace SmartFactory;

use SmartFactory\Interfaces\IMessageManager;

/**
 * Class for working with messages - errors, warnings etc.
 *
 * @since 3.1.1
 * Important change 1.
 * @since 3.1.2
 * Important change 2.
 *
 * @author Oleg Schildt
 */
class MessageManager implements IMessageManager
{
    use TestTrait;
    
    /**
     * Internal variable for storing the auto hide time
     * for the info messages with flag auto_hide = true.
     *
     * @var int
     *
     * @see MessageManager::getAutoHideTime()
     *
     * @author Oleg Schildt
     */
    protected static $auto_hide_time = 3;
    
    /**
     * Internal variable for storing the reference to session variables.
     *
     * @var array
     *
     * @author Oleg Schildt
     */
    protected static $session_vars = null;
    
    /**
     * Internal variable for storing the state of programming warnings - active or not.
     *
     * @var boolean
     *
     * @see MessageManager::detailsActive()
     * @see MessageManager::enableDetails()
     * @see MessageManager::disableDetails()
     *
     * @author Oleg Schildt
     */
    protected static $details_disabled = false;
    
    /**
     * Internal variable for storing the state of programming warnings - active or not.
     *
     * @var boolean
     *
     * @see MessageManager::progWarningsActive()
     * @see MessageManager::enableProgWarnings()
     * @see MessageManager::disableProgWarnings()
     *
     * @author Oleg Schildt
     */
    protected static $prog_warnings_disabled = false;

    /**
     * This is internal auxiliary function for preparing messags for displaying.
     *
     * When the messages are requested, it is assumed they will be displayed. Thus,
     * the message array is cleared to avoid displaying of the same messages twice.
     *
     * The message details are removed if the flag show_message_details is false.
     *
     * @param array &$messages
     * The source array of messages.
     *
     * @return array
     * Returns the array of messages for displaying.
     *
     * @author Oleg Schildt
     */
    protected function extractMessagesForDisplay(&$messages)
    {
        $output = [];
        
        if (empty($messages) || count($messages) == 0) {
            return $output;
        }
        
        foreach ($messages as $current) {
            $message_entry["message"] = $current["message"];
            
            if (!empty($current["code"])) {
                $message_entry["code"] = $current["code"];
            }
            
            if (!empty($current["auto_hide"])) {
                $message_entry["auto_hide"] = $this->getAutoHideTime();
            }
            
            if (!empty($current["details"]) && $this->detailsActive()) {
                $message_entry["details"] = $current["details"];
            }
            
            $output[] = $message_entry;
        } // foreach
        
        // unset messages because they has been shown
        $messages = null;
        
        return $output;
    } // extractMessagesForDisplay
    
    /**
     * Constructor.
     *
     * @author Oleg Schildt
     */
    public function __construct()
    {
        self::$session_vars = &session()->vars();
    } // __construct
    
    /**
     * Initializes the message manager.
     *
     * @param array $parameters
     * Initialization parameters as an associative array in the form key => value:
     *
     * - $parameters["auto_hide_time"] - server address.
     *
     * @return boolean
     * Returns true upon successful initialization, otherwise false.
     *
     * @author Oleg Schildt
     */
    public function init($parameters)
    {
        if (!empty($parameters["auto_hide_time"])) {
            self::$auto_hide_time = $parameters["auto_hide_time"];
        }
        
        return true;
    } // init
    
    /**
     * Sets the element to be focused.
     *
     * @param string $element
     * The ID of the element to be focused.
     *
     * @return void
     *
     * @see MessageManager::setFocusElement()
     *
     * @author Oleg Schildt
     */
    public function setFocusElement($element)
    {
        self::$session_vars["focus_element"] = $element;
    } // setFocusElement
    
    /**
     * Returns the element to be focused.
     *
     * When this method is called, it is assumed that the element will be focused. Thus,
     * the stored element is cleared to avoid focusing of the same element twice.
     *
     * @return string
     * Returns the ID of the element to be focused.
     *
     * @see MessageManager::getFocusElement()
     *
     * @author Oleg Schildt
     */
    public function getFocusElement()
    {
        if (empty(self::$session_vars["focus_element"])) {
            return "";
        }
        
        $felm = self::$session_vars["focus_element"];
        
        unset(self::$session_vars["focus_element"]);
        
        return $felm;
    } // getFocusElement
    
    /**
     * Sets the tab to be activated.
     *
     * @param string $tab
     * The ID of the tab to be activated.
     *
     * @return void
     *
     * @see MessageManager::getActiveTab()
     *
     * @author Oleg Schildt
     */
    public function setActiveTab($tab)
    {
        self::$session_vars["active_tab"] = $tab;
    } // setActiveTab
    
    /**
     * Returns the tab to be activated.
     *
     * When this method is called, it is assumed that the tab will be activated. Thus,
     * the stored active tab is cleared to avoid activating of the same tab twice.
     *
     * @return string
     * Returns the ID of the tab to be activated.
     *
     * @see MessageManager::setActiveTab()
     *
     * @author Oleg Schildt
     */
    public function getActiveTab()
    {
        if (empty(self::$session_vars["active_tab"])) {
            return "";
        }
        
        $atab = self::$session_vars["active_tab"];
        
        unset(self::$session_vars["active_tab"]);
        
        return $atab;
    } // getActiveTab
    
    /**
     * Sets the field to be highlighted.
     *
     * @param string $element
     * The ID of the field to be highlighted.
     *
     * @return void
     *
     * @see MessageManager::getErrorElement()
     *
     * @author Oleg Schildt
     */
    public function setErrorElement($element)
    {
        self::$session_vars["focus_element"] = $element;
        self::$session_vars["error_element"] = $element;
    } // setErrorElement
    
    /**
     * Returns the field to be highlighted.
     *
     * When this method is called, it is assumed that element will be highlighted. Thus,
     * the stored element is cleared to avoid highlighting of the same element twice.
     *
     * @return string
     * Returns the ID of the field to be highlighted.
     *
     * @see MessageManager::setErrorElement()
     *
     * @author Oleg Schildt
     */
    public function getErrorElement()
    {
        if (empty(self::$session_vars["error_element"])) {
            return "";
        }
        
        $felm = self::$session_vars["error_element"];
        
        unset(self::$session_vars["error_element"]);
        
        return $felm;
    } // getErrorElement
    
    /**
     * Stores the error to be reported.
     *
     * @param string $message
     * The error message to be reported.
     *
     * @param string $details
     * The error details to be reported. Here, more
     * technical details should be placed. Displaying
     * of this part might be controlled over a option
     * "display message details".
     *
     * @param string $code
     * The error code to be reported.
     *
     * @return void
     *
     * @see MessageManager::getErrors()
     * @see MessageManager::clearErrors()
     * @see MessageManager::errorsExist()
     *
     * @author Oleg Schildt
     */
    public function setError($message, $details = "", $code = "")
    {
        if (empty($message)) {
            return;
        }
        
        // we keep messages in the session
        // until we are sure they are shown
        // otherwise some messages may be lost
        // because of redirection
        self::$session_vars["errors"][$message] = ["message" => $message, "details" => $details, "code" => $code];
    } // setError
    
    /**
     * Clears the stored error messages.
     *
     * @return void
     *
     * @see MessageManager::setError()
     * @see MessageManager::clearWarnings()
     * @see MessageManager::clearProgWarnings()
     * @see MessageManager::clearDebugMessages()
     * @see MessageManager::clearInfos()
     * @see MessageManager::clearAll()
     *
     * @author Oleg Schildt
     */
    public function clearErrors()
    {
        unset(self::$session_vars["errors"]);
    } // clearErrors
    
    /**
     * Checks whether a stored error message exist.
     *
     * @return boolean
     * Returns true if the stored error message exists, otherwise false.
     *
     * @see MessageManager::setError()
     * @see MessageManager::warningsExist()
     * @see MessageManager::progWarningsExist()
     * @see MessageManager::debugMessageExists()
     * @see MessageManager::infosExist()
     *
     * @author Oleg Schildt
     */
    public function errorsExist()
    {
        return (isset(self::$session_vars["errors"]) && count(self::$session_vars["errors"]) > 0);
    } // errorsExist
    
    /**
     * Returns the array of errors if any have been stored.
     *
     * When the messages are requested, it is assumed they will be displayed. Thus,
     * the message array is cleared to avoid displaying of the same messages twice.
     *
     * @return array
     * Returns the array of errors if any have been stored.
     *
     * @see MessageManager::setError()
     * @see MessageManager::getWarnings()
     * @see MessageManager::getProgWarnings()
     * @see MessageManager::getDebugMessages()
     * @see MessageManager::getInfos()
     *
     * @author Oleg Schildt
     */
    public function getErrors()
    {
        // we keep messages in the session
        // until we are sure they are shown (calling get... means they are being shown)
        // otherwise some messages may be lost
        // because of redirection
        if (!$this->errorsExist()) {
            return [];
        }
        
        return $this->extractMessagesForDisplay(self::$session_vars["errors"]);
    } // getErrors
    
    /**
     * Stores the warning to be reported.
     *
     * @param string $message
     * The warning message to be reported.
     *
     * @param string $details
     * The warning details to be reported. Here, more
     * technical details should be placed. Displaying
     * of this part might be controlled over a option
     * "display message details".
     *
     * @return void
     *
     * @see MessageManager::getWarnings()
     * @see MessageManager::clearWarnings()
     * @see MessageManager::warningsExist()
     *
     * @author Oleg Schildt
     */
    public function setWarning($message, $details = "")
    {
        if (empty($message)) {
            return;
        }
        
        // we keep messages in the session
        // until we are sure they are shown
        // otherwise some messages may be lost
        // because of redirection
        self::$session_vars["warnings"][$message] = ["message" => $message, "details" => $details];
    } // setWarning
    
    /**
     * Clears the stored warning messages.
     *
     * @return void
     *
     * @see MessageManager::setWarning()
     * @see MessageManager::clearErrors()
     * @see MessageManager::clearProgWarnings()
     * @see MessageManager::clearDebugMessages()
     * @see MessageManager::clearInfos()
     * @see MessageManager::clearAll()
     *
     * @author Oleg Schildt
     */
    public function clearWarnings()
    {
        unset(self::$session_vars["warnings"]);
    } // clearWarnings
    
    /**
     * Checks whether a stored error warning exist.
     *
     * @return boolean
     * Returns true if the stored warning message exists, otherwise false.
     *
     * @see MessageManager::setWarning()
     * @see MessageManager::errorsExist()
     * @see MessageManager::progWarningsExist()
     * @see MessageManager::debugMessageExists()
     * @see MessageManager::infosExist()
     *
     * @author Oleg Schildt
     */
    public function warningsExist()
    {
        return (isset(self::$session_vars["warnings"]) && count(self::$session_vars["warnings"]) > 0);
    } // warningsExist
    
    /**
     * Returns the array of warnings if any have been stored.
     *
     * When the messages are requested, it is assumed they will be displayed. Thus,
     * the message array is cleared to avoid displaying of the same messages twice.
     *
     * @return array
     * Returns the array of warnings if any have been stored.
     *
     * @see MessageManager::setWarning()
     * @see MessageManager::getErrors()
     * @see MessageManager::getProgWarnings()
     * @see MessageManager::getDebugMessages()
     * @see MessageManager::getInfos()
     *
     * @author Oleg Schildt
     */
    public function getWarnings()
    {
        // we keep messages in the session
        // until we are sure they are shown (calling get... means they are being shown)
        // otherwise some messages may be lost
        // because of redirection
        if (!$this->warningsExist()) {
            return [];
        }
        
        return $this->extractMessagesForDisplay(self::$session_vars["warnings"]);
    } // getWarnings
    
    /**
     * Stores the programming warning to be reported.
     *
     * Programming warnings are shown only if the option
     * "show programming warning" is active.
     *
     * @param string $message
     * The programming warning message to be reported.
     *
     * @param string $details
     * The programming warning details to be reported. Here, more
     * technical details should be placed. Displaying
     * of this part might be controlled over a option
     * "display message details".
     *
     * @return void
     *
     * @see MessageManager::getProgWarnings()
     * @see MessageManager::clearProgWarnings()
     * @see MessageManager::progWarningsExist()
     *
     * @author Oleg Schildt
     */
    public function setProgWarning($message, $details = "")
    {
        if (empty($message) || !$this->progWarningsActive()) {
            return;
        }
        
        // we keep messages in the session
        // until we are sure they are shown
        // otherwise some messages may be lost
        // because of redirection
        self::$session_vars["prog_warnings"][$message] = ["message" => $message, "details" => $details];
    } // setProgWarning
    
    /**
     * Clears the stored programming warning messages.
     *
     * @return void
     *
     * @see MessageManager::setProgWarning()
     * @see MessageManager::clearErrors()
     * @see MessageManager::clearWarnings()
     * @see MessageManager::clearDebugMessages()
     * @see MessageManager::clearInfos()
     * @see MessageManager::clearAll()
     *
     * @author Oleg Schildt
     */
    public function clearProgWarnings()
    {
        unset(self::$session_vars["prog_warnings"]);
    } // clearProgWarnings
    
    /**
     * Checks whether a stored programming warning exist.
     *
     * @return boolean
     * Returns true if the stored programming warning message exists, otherwise false.
     *
     * @see MessageManager::setProgWarning()
     * @see MessageManager::errorsExist()
     * @see MessageManager::warningsExist()
     * @see MessageManager::debugMessageExists()
     * @see MessageManager::infosExist()
     *
     * @author Oleg Schildt
     */
    public function progWarningsExist()
    {
        return (isset(self::$session_vars["prog_warnings"]) && count(self::$session_vars["prog_warnings"]) > 0);
    } // progWarningsExist
    
    /**
     * Returns the array of programming warnings if any have been stored.
     *
     * When the messages are requested, it is assumed they will be displayed. Thus,
     * the message array is cleared to avoid displaying of the same messages twice.
     *
     * @return array
     * Returns the array of programming warnings if any have been stored.
     *
     * @see MessageManager::setProgWarning()
     * @see MessageManager::getErrors()
     * @see MessageManager::getWarnings()
     * @see MessageManager::getDebugMessages()
     * @see MessageManager::getInfos()
     *
     * @author Oleg Schildt
     */
    public function getProgWarnings()
    {
        // we keep messages in the session
        // until we are sure they are shown (calling get... means they are being shown)
        // otherwise some messages may be lost
        // because of redirection
        if (!$this->progWarningsExist()) {
            return [];
        }
        
        return $this->extractMessagesForDisplay(self::$session_vars["prog_warnings"]);
    } // getProgWarnings
    
    /**
     * Stores the debugging message to be reported.
     *
     * Displaying of the debugging messages might be
     * implemented to simplify the debugging process,
     * e.g. to the browser console or in a lightbox.
     *
     * @param string $message
     * The debugging message message to be reported.
     *
     * @param string $details
     * The debugging message details to be reported. Here, more
     * technical details should be placed. Displaying
     * of this part might be controlled over a option
     * "display message details".
     *
     * @return void
     *
     * @see MessageManager::getDebugMessages()
     * @see MessageManager::clearDebugMessages()
     * @see MessageManager::debugMessageExists()
     *
     * @author Oleg Schildt
     */
    public function setDebugMessage($message, $details = "")
    {
        if (empty($message)) {
            return;
        }
        
        // we keep messages in the session
        // until we are sure they are shown
        // otherwise some messages may be lost
        // because of redirection
        self::$session_vars["debug"][$message] = ["message" => $message, "details" => $details];
    } // setDebugMessage
    
    /**
     * Clears the stored debugging messages.
     *
     * @return void
     *
     * @see MessageManager::setDebugMessage()
     * @see MessageManager::clearErrors()
     * @see MessageManager::clearWarnings()
     * @see MessageManager::clearProgWarnings()
     * @see MessageManager::clearInfos()
     * @see MessageManager::clearAll()
     *
     * @author Oleg Schildt
     */
    public function clearDebugMessages()
    {
        unset(self::$session_vars["debug"]);
    } // clearDebugMessages
    
    /**
     * Checks whether a stored debugging message exist.
     *
     * @return boolean
     * Returns true if the stored debugging message exists, otherwise false.
     *
     * @see MessageManager::setDebugMessage()
     * @see MessageManager::errorsExist()
     * @see MessageManager::warningsExist()
     * @see MessageManager::progWarningsExist()
     * @see MessageManager::infosExist()
     *
     * @author Oleg Schildt
     */
    public function debugMessageExists()
    {
        return (isset(self::$session_vars["debug"]) && count(self::$session_vars["debug"]) > 0);
    } // debugMessageExists
    
    /**
     * Returns the array of debugging messages if any have been stored.
     *
     * When the messages are requested, it is assumed they will be displayed. Thus,
     * the message array is cleared to avoid displaying of the same messages twice.
     *
     * @return array
     * Returns the array of debugging messages if any have been stored.
     *
     * @see MessageManager::setDebugMessage()
     * @see MessageManager::getErrors()
     * @see MessageManager::getWarnings()
     * @see MessageManager::getProgWarnings()
     * @see MessageManager::getInfos()
     *
     * @author Oleg Schildt
     */
    public function getDebugMessages()
    {
        // we keep messages in the session
        // until we are sure they are shown (calling get... means they are being shown)
        // otherwise some messages may be lost
        // because of redirection
        if (!$this->debugMessageExists()) {
            return [];
        }
        
        return $this->extractMessagesForDisplay(self::$session_vars["debug"]);
    } // getDebugMessages
    
    /**
     * Stores the information message to be reported.
     *
     * @param string $message
     * The information message to be reported.
     *
     * @param string $details
     * The information message details to be reported. Here, more
     * technical details should be placed. Displaying
     * of this part might be controlled over a option
     * "display message details".
     *
     * @param boolean $auto_hide
     * The flag that controls whether the message box should be closed
     * automatically after a time defined by the initialization.
     *
     * @return void
     *
     * @see MessageManager::getInfos()
     * @see MessageManager::clearInfos()
     * @see MessageManager::infosExist()
     *
     * @author Oleg Schildt
     */
    public function setInfo($message, $details = "", $auto_hide = false)
    {
        if (empty($message)) {
            return;
        }
        
        // we keep messages in the session
        // until we are sure they are shown
        // otherwise some messages may be lost
        // because of redirection
        self::$session_vars["infos"][$message] = [
            "message" => $message,
            "details" => $details,
            "auto_hide" => $auto_hide
        ];
    } // setInfo
    
    /**
     * Clears the stored information messages.
     *
     * @return void
     *
     * @see MessageManager::setInfo()
     * @see MessageManager::clearErrors()
     * @see MessageManager::clearWarnings()
     * @see MessageManager::clearProgWarnings()
     * @see MessageManager::clearDebugMessages()
     * @see MessageManager::clearAll()
     *
     * @author Oleg Schildt
     */
    public function clearInfos()
    {
        unset(self::$session_vars["infos"]);
    } // clearInfos
    
    /**
     * Checks whether an information message exist.
     *
     * @return boolean
     * Returns true if the information message exists, otherwise false.
     *
     * @see MessageManager::setInfo()
     * @see MessageManager::errorsExist()
     * @see MessageManager::warningsExist()
     * @see MessageManager::progWarningsExist()
     * @see MessageManager::debugMessageExists()
     *
     * @author Oleg Schildt
     */
    public function infosExist()
    {
        return (isset(self::$session_vars["infos"]) && count(self::$session_vars["infos"]) > 0);
    } // infosExist
    
    /**
     * Returns the array of information messages if any have been stored.
     *
     * When the messages are requested, it is assumed they will be displayed. Thus,
     * the message array is cleared to avoid displaying of the same messages twice.
     *
     * @return array
     * Returns the array of information messages if any have been stored.
     *
     * @see MessageManager::setInfo()
     * @see MessageManager::getErrors()
     * @see MessageManager::getWarnings()
     * @see MessageManager::getProgWarnings()
     * @see MessageManager::getDebugMessages()
     *
     * @author Oleg Schildt
     */
    public function getInfos()
    {
        // we keep messages in the session
        // until we are sure they are shown (calling get... means they are being shown)
        // otherwise some messages may be lost
        // because of redirection
        if (!$this->infosExist()) {
            return [];
        }
        
        return $this->extractMessagesForDisplay(self::$session_vars["infos"]);
    } // getInfos
    
    /**
     * Clears all stored messages and active elements.
     *
     * @return void
     *
     * @see MessageManager::clearErrors()
     * @see MessageManager::clearWarnings()
     * @see MessageManager::clearProgWarnings()
     * @see MessageManager::clearDebugMessages()
     * @see MessageManager::clearInfos()
     *
     * @author Oleg Schildt
     */
    public function clearAll()
    {
        $this->clearErrors();
        $this->clearWarnings();
        $this->clearProgWarnings();
        $this->clearDebugMessages();
        $this->clearInfos();
        
        unset(self::$session_vars["focus_element"]);
        unset(self::$session_vars["error_element"]);
        unset(self::$session_vars["active_tab"]);
    } // clearAll
    
    /**
     * Returns the auto hide time in seconds for the info
     * messages with flag auto_hide = true.
     *
     * @return int
     * Returns the auto hide time in seconds for the info
     * messages with flag auto_hide = true.
     *
     * @author Oleg Schildt
     */
    public function getAutoHideTime()
    {
        return self::$auto_hide_time;
    } // getAutoHideTime
    
    /**
     * Add all stored existing messages to the response.
     *
     * When the messages are requested, it is assumed they will be displayed. Thus,
     * the message array is cleared to avoid displaying of the same messages twice.
     *
     * @param string &$response
     * The target array where the messages should be added.
     *
     * @return void
     *
     * @author Oleg Schildt
     */
    public function addMessagesToResponse(&$response)
    {
        if ($this->infosExist()) {
            $response["INFO_MESSAGES"] = $this->getInfos();
        }
        
        if ($this->warningsExist()) {
            $response["WARNING_MESSAGES"] = $this->getWarnings();
        }
        
        if ($this->errorsExist()) {
            $response["ERROR_MESSAGES"] = $this->getErrors();
        }
        
        if ($this->progWarningsExist()) {
            $response["PROG_WARNINGS"] = $this->getProgWarnings();
        }
        
        if ($this->debugMessageExists()) {
            $response["DEBUG_MESSAGES"] = $this->getDebugMessages();
        }
        
        $response["AUTO_HIDE_TIME"] = $this->getAutoHideTime();
        
        $response["FOCUS_ELEMENT"] = $this->getFocusElement();
        
        $response["ACTIVE_TAB"] = $this->getActiveTab();
        
        $response["ERROR_ELEMENT"] = $this->getErrorElement();
    } // addMessagesToResponse
    
    /**
     * Returns the state whether the display of the message details is active or not.
     *
     * If the display of the message details is active, they are shown in the frontend.
     *
     * The message details are generally managed over the setting show_prog_warning.
     * But you can also temporarily disable message details, e.g. to avoid displaying of
     * unnecessary warnings when you make a check that can produce a programming warning,
     * but it is a controlled noticed and should not bother the user.
     *
     * @return boolean
     * Returns the state whether the display of the message details is active or not.
     *
     * @see MessageManager::enableDetails()
     * @see MessageManager::disableDetails()
     *
     * @author Oleg Schildt
     */
    public function detailsActive()
    {
        return empty(self::$details_disabled);
    } // detailsActiveActive
    
    /**
     * Enables the display of the message details.
     *
     * If the display of the message details is active, they are shown in the frontend.
     *
     * @return void
     *
     * @see MessageManager::detailsActive()
     * @see MessageManager::disableDetails()
     *
     * @author Oleg Schildt
     */
    public function enableDetails()
    {
        self::$details_disabled = false;
    } // enableDetails
    
    /**
     * Disables the display of the message details.
     *
     * If the display of the message details is active, they are shown in the frontend.
     *
     * @return void
     *
     * @see MessageManager::detailsActive()
     * @see MessageManager::enableDetails()
     *
     * @author Oleg Schildt
     */
    public function disableDetails()
    {
        self::$details_disabled = true;
    } // disableDetails

    /**
     * Returns the state whether the display of the programming warnings is active or not.
     *
     * If the display of the programming warnings is active, they are shown in the frontend.
     *
     * The programming warnings are generally managed over the setting show_prog_warning.
     * But you can also temporarily disable programming warnings, e.g. to avoid displaying of
     * unnecessary warnings when you make a check that can produce a programming warning,
     * but it is a controlled noticed and should not bother the user.
     *
     * @return boolean
     * Returns the state whether the display of the programming warnings is active or not.
     *
     * @see MessageManager::enableProgWarnings()
     * @see MessageManager::disableProgWarnings()
     *
     * @author Oleg Schildt
     */
    public function progWarningsActive()
    {
        return empty(self::$prog_warnings_disabled);
    } // detailsActive
    
    /**
     * Enables the display of the programming warnings.
     *
     * If the display of the programming warnings is active, they are shown in the frontend.
     *
     * @return void
     *
     * @see MessageManager::progWarningsActive()
     * @see MessageManager::disableProgWarnings()
     *
     * @author Oleg Schildt
     */
    public function enableProgWarnings()
    {
        self::$prog_warnings_disabled = false;
    } // enableProgWarnings
    
    /**
     * Disables the display of the programming warnings.
     *
     * If the display of the programming warnings is active, they are shown in the frontend.
     *
     * @return void
     *
     * @see MessageManager::progWarningsActive()
     * @see MessageManager::enableProgWarnings()
     *
     * @author Oleg Schildt
     */
    public function disableProgWarnings()
    {
        self::$prog_warnings_disabled = true;
    } // disableProgWarnings
} // MessageManager
