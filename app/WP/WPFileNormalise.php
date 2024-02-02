<?php

namespace App\WP;

use DOMDocument;
use DOMElement;

class WPFileNormalise
{
    // Todo: 1. Remove class and prepare for convertion.
    public function extractMainImage($html)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $topLevelElements = $dom->documentElement->childNodes;
        // ray($topLevelElements->length,"Hello");
        // Process each top-level element
        // foreach ($topLevelElements as $element) {
        //     ray('WTF', $element);
        //     // Only process element nodes (not text nodes)
        //     if ($element instanceof DOMElement) {
        //         $figures = $element->getElementsByTagName('figure'); // Find figures within this element

        //         // Replace each figure with the extracted image tag
        //         foreach ($figures as $figure) {
        //             $mainImage = $this->extractImageFromFigure($figure);
        //             ray($mainImage);
        //             if ($mainImage) {
        //                 $figure->parentNode->replaceChild($dom->createDocumentFragment()->appendXML($mainImage), $figure);
        //             }
        //         }
        //     }
        // }

        foreach ($topLevelElements as $element) {
            if ($element->nodeName === 'figure') {
                $mainImage = $this->extractImageFromFigure($element);

                if ($mainImage) {
                    // Create a document fragment and append the image tag
                    $fragment = $dom->createDocumentFragment();
                    $fragment->appendXML($mainImage);

                    // Replace the figure with the fragment (corrected argument)
                    $element->parentNode->replaceChild($fragment, $element);
                }
            }
        }
        // Return the modified HTML content
        return $dom->saveHTML();


        // $figures = $dom->getElementsByTagName('figure');


        // if ($figures->length > 0) {
        //     // $figure = $figures->item(0);
        //     // return $this->extractImageFromFigure($figure);
        //     foreach ($figures as $figure) {
        //         $mainImage = $this->extractImageFromFigure($figure);

        //         if ($mainImage) {
        //             $figure->parentNode->replaceChild($dom->createTextNode($mainImage), $figure);
        //         }
        //     }

        //     // Return the modified HTML with all figures replaced
        //     return $dom->saveHTML();
        // } else {
        //     ray('extractMainImage');
        //     return null;
        // }
    }

    protected function extractImageFromFigure($figureNode)
    {
        $images = $figureNode->getElementsByTagName('img');
        if ($images->length > 0) {
            $mainImage = $images->item(0);
            return $this->buildImageTag($mainImage);
        } else {
            ray('extractImageFromFigure');
            return null;
        }
    }

    protected function buildImageTag($imageNode)
    {
        $src = $imageNode->getAttribute('src');
        $alt = $imageNode->getAttribute('alt');
        $width = $imageNode->getAttribute('width');
        $height = $imageNode->getAttribute('height');

        return "<img src='$src' alt='$alt' width='$width' height='$height'>";
    }

    // public function extractAndModifyImages($html)
    // {
    //     $dom = new DOMDocument();
    //     libxml_use_internal_errors(true);
    //     $dom->loadHTML($html);
    //     libxml_clear_errors();

    //     $figures = $dom->getElementsByTagName('figure');
    //     ray($figures->length);
    //     if ($figures->length > 0) {
    //         ray($figures->item(0));
    //         $figure = $figures->item(0);
    //         return $this->modifyImagesInFigure($figure);
    //     } else {
    //         ray("No Fig");
    //         return null;
    //     }
    // }

    // protected function modifyImagesInFigure($figureNode)
    // {
    //     $images = $figureNode->getElementsByTagName('img');
    //     foreach ($images as $image) {
    //         $image->setAttribute('alt', ' '); // Apply modification
    //     }
    //     // return $figure->outerHTML; // Return modified figure content
    //     return $figureNode;
    // }


    public function removeClasses($html)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        $dom->loadHTML('<root>' . $html . '</root>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        // $elements = $dom->getElementsByTagName('*');

        // foreach ($elements as $element) {
        //     // Remove the "class" attribute from each element
        //     $element->removeAttribute('class');
        // }

        // // Get the updated HTML content
        // $cleanedHtml = $dom->saveHTML();

        // Extract the content of the <root> element
        $rootElement = $dom->getElementsByTagName('root')->item(0);
        if ($rootElement) {
            $newHtml = '';
            foreach ($rootElement->childNodes as $childNode) {
                $newHtml .= $dom->saveHTML($childNode);
            }

            // Remove unwanted elements (doctype and html tags)
            $newDom = new DOMDocument();
            libxml_use_internal_errors(true);
            $newDom->loadHTML($newHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();

            // Import nodes without the doctype and html root
            foreach ($newDom->childNodes as $child) {
                $importedNode = $dom->importNode($child, true);
                $dom->appendChild($importedNode);
            }
        }

        // Get all elements in the DOM
        $elements = $dom->getElementsByTagName('*');

        foreach ($elements as $element) {
            // Remove the "class" attribute from each element
            $element->removeAttribute('class');
        }

        // Get the updated HTML content
        $cleanedHtml = $dom->saveHTML();

        // Output the cleaned HTML
        return $cleanedHtml;
    }
}
