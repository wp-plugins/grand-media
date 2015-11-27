<?php
/**
 * PEL: PHP Exif Library.
 * A library with support for reading and
 * writing all Exif headers in JPEG and TIFF images using PHP.
 *
 * Copyright (C) 2015, Johannes Weberhofer.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program in the file COPYING; if not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor,
 * Boston, MA 02110-1301 USA
 */

/**
 * Autoloader for PEL
 * @param $class
 */

function gmedia_pel_autoloader($class){
    if (substr_compare($class, 'Pel', 0, 3) === 0) {
        $load = realpath(dirname(__FILE__) . '/' . $class . '.php');
        if ($load !== false) {
            include_once realpath($load);
        }
    }
}

/**
 * Register autoloader for PEL
 */
spl_autoload_register('gmedia_pel_autoloader');

