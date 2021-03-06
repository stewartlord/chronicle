<?php
/**
 * Encapsulates the results of a Perforce command.
 *
 * @copyright   2011 Perforce Software. All rights reserved.
 * @license     Please see LICENSE.txt in top-level folder of this distribution.
 * @version     <release>/<patch>
 */
class P4_Result
{
    protected   $_command;
    protected   $_data      =   array();
    protected   $_errors    =   array();
    protected   $_warnings  =   array();
    protected   $_isTagged  =   true;

    /**
     * Constructs the perforce command result object.
     *
     * @param   string  $command    the command that was run.
     * @param   array   $data       optional - array of result data.
     * @param   bool    $tagged     optional - true if data is tagged.
     */
    public function __construct($command, $data = null, $tagged = true)
    {
        $this->_command     =   $command;
        if (is_array($data)) {
            $this->_data    =   $data;
        }
        $this->_isTagged    =   $tagged;
    }

    /**
     * Return the name of the perforce command that was issued.
     *
     * @return  string  the command.
     */
    public function getCommand()
    {
        return $this->_command;
    }

    /**
     * Test if the output is tagged.
     *
     * @return  boolean true if the output is tagged.
     */
    public function isTagged()
    {
        return $this->_isTagged;
    }

    /**
     * Set data on the result object.
     *
     * @param   array  $data  the array of data to set on the result.
     * @return  P4_Result     provides a fluent interface
     */
    public function setData($data)
    {
        if (!is_array($data)) {
            $data = array($data);
        }
        $this->_data = $data;

        return $this;
    }

    /**
     * Set errors on the result object.
     *
     * @param   array  $errors  the error messages to set.
     */
    public function setErrors($errors)
    {
        if (!is_array($errors)) {
            $errors = array($errors);
        }
        $this->_errors = $errors;
    }

    /**
     * Set warnings on the result object.
     *
     * @param   array  $warnings  the warning messages to set.
     */
    public function setWarnings($warnings)
    {
        if (!is_array($warnings)) {
            $warnings = array($warnings);
        }
        $this->_warnings = $warnings;
    }

    /**
     * Add data to the result object.
     *
     * @param   string|array   $data   string value or array of attribute values.
     */
    public function addData($data)
    {
        $this->_data[] = $data;
    }

    /**
     * Set an error on the result object.
     *
     * @param   string  $error  the error message to set.
     */
    public function addError($error)
    {
        $this->_errors[] = (string) $error;
    }

    /**
     * Set a warning on the result object.
     *
     * @param   string  $warning  the warning message to set.
     */
    public function addWarning($warning)
    {
        $this->_warnings[] = (string) $warning;
    }

    /**
     * Return all result data or a particular index/attribute if specified.
     * You must specify a index if you wish to fetch a specific attribute.
     *
     * @param   integer             $index      optional - the set of attributes to get.
     * @param   mixed               $attribute  optional - a specific attribute to get.
     * @return  array|string|false  the requested result data or false if index/attribute invalid.
     */
    public function getData($index = null, $attribute = null)
    {
        // if no index is specified, return all data.
        if (!is_numeric($index)) {
            return $this->_data;
        }

        // if a valid index is specified without an attribute,
        // return the value at that index.
        if ($attribute === null && array_key_exists($index, $this->_data)) {
            return $this->_data[$index];
        }

        // if a valid index and attribute are specified return the attribute value.
        if ($attribute !== null &&
            array_key_exists($index, $this->_data) &&
            is_array($this->_data[$index]) &&
            array_key_exists($attribute, $this->_data[$index])) {
            return $this->_data[$index][$attribute];
        }

        return false;
    }

    /**
     * Return any errors encountered executing the command.
     * Errors have leading/trailing whitespace stripped to ensure consistency between
     * various connection methods.
     *
     * @return array    any errors set on the result object.
     */
    public function getErrors()
    {
        return array_map('trim', $this->_errors);
    }

    /**
     * Return any warnings encountered executing the command.
     * Warnings have leading/trailing whitespace stripped to ensure consistency between
     * various connection methods.
     * 
     * @return array    any warnings set on the result object.
     */
    public function getWarnings()
    {
        return array_map('trim', $this->_warnings);
    }

    /**
     * Check if this result contains data (as opposed to errors/warnings).
     *
     * @return  bool    true if there is data - false otherwise.
     */
    public function hasData()
    {
        return !empty($this->_data);
    }

    /**
     * Check if there are any errors set for this result.
     *
     * @return  bool    true if there are errors - false otherwise.
     */
    public function hasErrors()
    {
        return !empty($this->_errors);
    }

    /**
     * Check if there are any warnings set for this result.
     *
     * @return  bool    true if there are warnings - false otherwise.
     */
    public function hasWarnings()
    {
        return !empty($this->_warnings);
    }

    /**
     * Expands any numeric sequences present based on passed attribute identifier.
     *
     * @param   mixed       $attributes     accepts null (default) to expand all attributes,
     *                                      string specifying a single attribute or an array
     *                                      of attribute names.
     * @return  P4_Result   provides a fluent interface
     * @todo    Add support for indicies with commas (nested sequences)
     */
    public function expandSequences($attributes = null)
    {
        if (is_string($attributes)) {
            $attributes = array($attributes);
        }

        if ($attributes !== null && !is_array($attributes)) {
            throw new InvalidArgumentException('Attribute must be null, string or array of strings');
        }

        // expand specified numbered sequences in data array.
        for ($i = 0; $i < count($this->_data); $i++) {

            // skip any data blocks that are not in array format
            if (!is_array($this->_data[$i])) {
                continue;
            }

            foreach ($this->_data[$i] as $key => $value) {

                // pull sequences off of key (ie. View0, View1, ...).
                // skips entry if it doesn't have a trailing number
                if (preg_match('/(.*?)([0-9]+)$/', $key, $matches) !== 1) {
                    continue;
                }

                // pull out the base and index
                $base  = $matches[1];
                $index = $matches[2];

                // if we have a specified list of attribute(s) and this
                // base isn't listed skip it.
                if ($attributes !== null && !in_array($base, $attributes)) {
                    continue;
                }

                // if base doesn't exist, initialize it to an array
                // if we already have an entry for base that isn't an array, skip expansion
                if (!array_key_exists($base, $this->_data[$i])) {
                    $this->_data[$i][$base] = array();
                } else if (!is_array($this->_data[$i][$base])) {
                    continue;
                }

                $this->_data[$i][$base][$index] = $value;
                unset($this->_data[$i][$key]);
            }
        }

        return $this;
    }
}
