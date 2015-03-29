<?php

namespace main\dbBundle\Func;

class FileFunctions {

    /**
     * Function to create the file to export
     * @param String $filename name of file
     * @return File The file created
     */
    public function createCsvFile($filename) {
        $handle = fopen($filename, "w");
        fwrite($handle, "sep=, \n");
        fwrite($handle, "Date,Last Name,First Name,Trigram,Firm,Department,Team,Nature,Final Customer,Partner,Product,Component,Plateform,Version,Activity,Time Spent,Task label,commentary\n");
        return $handle;
    }

    /**
     * Function to write the file to download
     * @param Array $arraysToWrite
     * @param String $filename name of file
     */
    function writeToCsvFile($arraysToWrite, $filename) {
        $handle = FileFunctions::createCsvFile($filename);
        foreach ($arraysToWrite as $arrayToWrite) {
            foreach ($arrayToWrite as $element) {
                $i = 0;
                foreach ($element as $value) {
                    foreach ($value as $item) {
                        if ($i == 15) {
                            fwrite($handle, utf8_decode(str_replace(".", ",", "\"" . $item . "\"")));
                        }
                        else {
                            fwrite($handle, utf8_decode(str_replace(".", ",", "\"" . $item . "\"")));
                        }
                        fwrite($handle, ",");
                        $i++;
                    }
                    fwrite($handle, "\n");
                }
            }
        }
        fclose($handle);
    }
    
    /**
     * Function to get the name of the file
     * @return String File name with the date
     */
    static public function functionName() {
        return ("export_gtt".date("Y_m_d"));
    }

    /**
     * Function to download the file with tasks
     * @param String $filename name of file
     */
    function downloadCsvFile($filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . basename($filename));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
    }

    /**
     * Function to delete the file once downloaded
     * @param String $filename Name of file to delete
     */
    function removeFile($filename) {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

}
