<?php

namespace Multiclass;

defined( 'ABSPATH' ) || exit;

use DOMDocument;

class Pictogram {
    public function __construct() {
        add_filter('learndash_quiz_question_answer_preprocess', [$this, 'convertTableToQuizGrid'], 10, 2);
    }

    public function convertTableToQuizGrid($answer_text, $context) {
        if($context === 'cloze') {
            if (strpos($answer_text, 'table') === false || strpos($answer_text, 'tbody') === false || strpos($answer_text, '{yes}') === false || strpos($answer_text, '{no}') === false) {
                return $answer_text;
            }

            // Load HTML into DOMDocument
            $dom = new DOMDocument();
            // Prevent HTML5 parsing errors
            libxml_use_internal_errors(true);
            $dom->loadHTML($answer_text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            // Create output container
            $output = '<div class="pictogramm-quiz-grid">' . PHP_EOL;
            
            // Find all table cells
            $cells = $dom->getElementsByTagName('td');
            
            foreach ($cells as $cell) {
                // Get the original HTML content of the cell
                $cellHtml = $dom->saveHTML($cell);
                
                // Extract image source
                preg_match('/<img[^>]+src=([\'"])(.*?)\1/', $cellHtml, $imgMatch);
                $imgSrc = isset($imgMatch[2]) ? $imgMatch[2] : '';
                
                // Extract the complete content within curly braces including the braces
                preg_match('/{[^}]*}/', $cellHtml, $matches);
                $bracesContent = isset($matches[0]) ? $matches[0] : '';
                
                // Create quiz item div
                $output .= str_repeat(' ', 8) . '<div class="quiz-item">' . PHP_EOL;
                $output .= str_repeat(' ', 12) . '<img src="' . htmlspecialchars($imgSrc) . '">' . PHP_EOL;
                $output .= str_repeat(' ', 12) . $bracesContent . PHP_EOL;
                $output .= str_repeat(' ', 8) . '</div>' . PHP_EOL;
            }
            
            $output .= '</div>';
            
            return $output;
        }
        return $answer_text;
    }
}