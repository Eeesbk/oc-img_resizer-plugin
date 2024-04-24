<?php namespace Eeesbk\ImgResizer;

use System\Classes\PluginBase;
use Eeesbk\ImgResizer\Classes\Image;
use Validator;

/**
 * ImgResizer Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'eeesbk.imgresizer::lang.plugin.name',
            'description' => 'eeesbk.imgresizer::lang.plugin.description',
            'author'      => 'Eeesbk',
            'icon'        => 'icon-picture-o',
            'homepage'    => 'https://github.com/eeesbk/oc-img_resizer-plugin'
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'eeesbk.imgresizer.access_settings' => [
                'tab'   => 'eeesbk.imgresizer::lang.permission.tab',
                'label' => 'eeesbk.imgresizer::lang.permission.label'
            ]
        ];
    }

    public function boot(){
        Validator::extend('valid_tinypng_key', function($attribute, $value, $parameters) {
            try {
                \Tinify\setKey($value);
                \Tinify\validate();
            } catch(\Tinify\Exception $e) {
                return false;
            }

            return true;
        });
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'resize' => function($file_path, $width = false, $height = false, $options = []) {
                    $image = new Image($file_path);
                    return $image->resize($width, $height, $options);
                },
                'imageWidth' => function($image) {
                    if (!$image instanceOf Image) {
                        $image = new Image($image);
                    }
                    return getimagesize($image->getCachedImagePath())[0];
                },
                'imageHeight' => function($image) {
                    if (!$image instanceOf Image) {
                        $image = new Image($image);
                    }
                    return getimagesize($image->getCachedImagePath())[1];
                }
            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'eeesbk.imgresizer::lang.settings.label',
                'icon'        => 'icon-picture-o',
                'description' => 'eeesbk.imgresizer::lang.settings.description',
                'class'       => 'Eeesbk\ImgResizer\Models\Settings',
                'order'       => 0,
                'permissions' => ['eeesbk.imgresizer.access_settings']
            ]
        ];
    }

    public function registerListColumnTypes()
    {
        return [
            'thumb' => [$this, 'evalThumbListColumn'],
        ];
    }

    public function evalThumbListColumn($value, $column, $record)
    {
        $config = $column->config;

        // Get config options with defaults
        $width = isset($config['width']) ? $config['width'] : 50;
        $height = isset($config['height']) ? $config['height'] : 50;
        $options = isset($config['options']) ? $config['options'] : [];

        // attachMany relation?
        if (isset($record->attachMany[$column->columnName]))
        {
            $file = $value->first();
        }
        // attachOne relation?
        else if (isset($record->attachOne[$column->columnName]))
        {
            $file = $value;
        }
        // Mediafinder
        else
        {
            $file = storage_path() . '/app/media' . $value;
        }

        $image = new Image($file);
        return $image->resize($width, $height, $options)->render();
    }
}
