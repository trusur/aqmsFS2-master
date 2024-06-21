<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the frameworks
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @link: https://codeigniter4.github.io/CodeIgniter4/
 */

use App\Models\m_configuration;

if(!function_exists('get_config')) {
    /**
     * Retrieves the configuration content based on the provided name.
     *
     * @param mixed $name The name of the configuration to retrieve.
     * @throws Exception Description of the exception.
     * @return mixed The content of the configuration.
     */
    function get_config($name, $default = null) {
       try{
            $configurationM  = new m_configuration();
            $content = $configurationM->where('name', $name)->first();
            return $content->content ?? $default;
       }catch(Exception $e){
            return $default;
       }
    }
}
if(!function_exists('update_config')) {
    function update_config($name, $content) {
       try{
            if(get_config($name) == null){
                return set_config($name, $content);
            }
            $configurationM  = new m_configuration();
            return $configurationM->where('name', $name)->update(['content' => $content]);
       }catch(Exception $e){
            return false;
       }
    }
}
if(!function_exists('set_config')) {
    function set_config($name, $content) {
       try{
            $configurationM  = new m_configuration();
            return $configurationM->insert(['name' => $name, 'content' => $content]);
       }catch(Exception $e){
            return false;
       }
    }
}