<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ==============================================================
 *
 * Octave
 *
 * ==============================================================
 *
 * @copyright  2014 Richard Lobb, University of Canterbury
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('application/libraries/LanguageTask.php');

class Octave_Task extends Task {
    public function __construct($source, $filename, $input, $params) {
        Task::__construct($source, $filename, $input, $params);
    }

    public static function getVersion() {
        return 'Octave 3.6.4';
    }

    public function compile() {
        $this->executableFileName = $this->sourceFileName . '.m';
        if (!copy($this->sourceFileName, $this->executableFileName)) {
            throw new coding_exception("Octave_Task: couldn't copy source file");
        }
    }

    public function getRunCommand() {
         return array(
             '/usr/bin/octave',
             '--norc',
             '--no-window-system',
             '--silent',
             $this->sourceFileName
         );
     }


     // Remove return chars and delete the extraneous error: lines at the end
     public function filteredStderr() {
         $out1 = str_replace("\r", '', $this->stderr);
         $lines = explode("\n", $out1);
         while (count($lines) > 0 && trim($lines[count($lines) - 1]) === '') {
             array_pop($lines);
         }
         if (count($lines) > 0 && 
                 strpos($lines[count($lines) - 1],
                         'error: ignoring octave_execution_exception') === 0) {
             array_pop($lines);
         }
         
         // A bug in octave results in some errors lines at the end due to the
         // non-existence of some environment variables that we can't set up
         // in jobe. So trim them off.
         if (count($lines) >= 1 && 
                    $lines[count($lines) - 1] == 'error: No such file or directory') {
             array_pop($lines);
         }

         if (count($lines) > 0) {
            return implode("\n", $lines) . "\n";
         } else {
             return '';
         }
     }
}
