<?php

namespace App\Forms;

use Nette,
    Nette\Application\UI\Form;

/**
 * Formular pro upload fotografii
 * Class UploaderFactory
 * @package App\Forms
 */
class PhotoDownloaderFactory extends Nette\Object
{
    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $select = [];
        /**
         * @var \SplFileInfo $directory
         */
        try {
            foreach (Nette\Utils\Finder::findDirectories('*')->in('../www/gallery/albums') as $dirname => $directory) {
                $select[$directory->getFilename()] = $directory->getBasename();
            }
            ksort($select, SORT_STRING);
            $form->addSelect('directories', 'Vyberte fotogalerii', $select);
        } catch (\Exception $e) {
            //nebude zobrazen ciselnik, bude potreba vytvorit prvni folder pro fotogalerii
        }
        $form->addSubmit('UploadFiles', 'Stáhnout');

        return $form;
    }
}
