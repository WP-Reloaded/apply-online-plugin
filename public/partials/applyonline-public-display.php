<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Applyonline
 * @subpackage Applyonline/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<section class="aol-name-section">
    <select name="aol-name-title">
        <option value="Mr.">Mr.</option>
        <option value="Mrs.">Mrs.</option>
        <option value="Ms.">Ms.</option>
        <option value="Miss.">Miss</option>
        <option value="Dr.">Dr.</option>
    </select>
    <input type="text" name="aol-first-name">
    <input type="text" name="aol-middle-name">
    <input type="text" name="aol-last-name">
</section>
<section class="aol-experience-section">
    <input type="input" class="aol-full-wdith" name="aol-experience-title">
    <textarea name="aol-experience-description"></textarea>
    <div class="aol-experience-duration-section">
        <div class="aol-experience-dates">
            <input type="date" name="aol-experience-start-date">
            <input type="date" name="aol-experience-end-date">
        </div>
        <input type="checkbox" name="aol-experience-current">I currently work here.
    </div>
</section>