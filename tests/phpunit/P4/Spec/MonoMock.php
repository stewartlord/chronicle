<?php
/**
 * This is a test implementation of the SpecAbstract.
 * It is used to thoroughly exercise the base spec functionality so latter implementors
 * can focus on testing only their own additions/modifications.
 *
 * This class happens to represent the 'typemap' type as this is the simplest looking mono-spec.
 *
 * @copyright   2011 Perforce Software. All rights reserved.
 * @license     Please see LICENSE.txt in top-level folder of this distribution.
 * @version     <release>/<patch>
 */
class P4_Spec_MonoMock extends P4_SpecAbstract
{
    protected static    $_specType          = 'typemap';

    /**
     * This function provides the tests access to any protected functions.
     *
     * @param   string  $function   Name of function to be called on this object
     * @param   array|string    $params     Paramater(s) to pass, optional
     * @return  mixed   Return result of called function, False on error
     */
    public function callProtectedFunc($function, $params = array())
    {
        if (!is_array($params)) {
            $params = array($params);
        }
        return call_user_func_array(array($this, $function), $params);
    }

    /**
     * This function provides the tests access to any protected static functions.
     *
     * @param   string  $function   Name of function to be called on this object
     * @param   array|string    $params     Paramater(s) to pass, optional
     * @return  mixed   Return result of called function, False on error
     */
    public static function callProtectedStaticFunc($function, $params = array())
    {
        if (!is_array($params)) {
            $params = array($params);
        }
        return call_user_func_array('static::'.$function, $params);
    }

    /**
     * This function provides the tests set capabilities on protected static variables.
     *
     * @param   string  $name   Name of variable to set/update on this object
     * @param   mixed   $value  New value to use
     */
    public static function setProtectedStaticVar($name, $value)
    {
        static::${$name} = $value;
    }

    /**
     * Accessor function for test objects 'typemap' value.
     * As noted in header, the fact that this object represents typemap is unimportant.
     *
     * To verify the accessor is doing something, it appends a single 'A' to the end of string.
     *
     * This accessor is not enabled by default. An outside tester must use 'setProtected'
     * if they wish to enable accessor mapping.
     *
     * @return  string  Objects 'TypeMap' value with an 'A' appended to end.
     */
    public function getTypeMapAppendA()
    {
        $out = array();
        foreach ($this->_getValue('TypeMap') as $key => $value) {
            $out[$key] = $value . 'A';
        }

        return $out;
    }

    /**
     * Mutator function for test objects 'typemap' value.
     * As noted in header, the fact that this object represents typemap is unimportant.
     *
     * To verify the mutator is doing something, it removes a single 'A' from the end of string.
     * If the string is empty, or doesn't end with an 'a' no modification is done.
     *
     * This mutator is not enabled by default. An outside tester must use 'setProtectedVar'
     * if they wish to enable accessor mapping.
     *
     * @param   string  $typeMap  New 'TypeMap' value, will remove rightmost 'A' if present.
     */
    public function setTypeMapRemoveA($typeMap)
    {
        foreach ($typeMap as $key => $value) {
            if (substr($value, -1) == 'A') {
                $typeMap[$key] = substr($value, 0, -1);
            }
        }

        $this->_setValue('TypeMap', $typeMap);
    }
}
