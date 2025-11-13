<?php /* Template Name: Application Form */
get_header();
$main_content = get_field('main_content');
$application_form_part_title = get_field('application_form_part_title');
$part_titles = get_field('part_titles');
$this_is_what_happens_after_you_apply = get_field('this_is_what_happens_after_you_apply');
$after_you_apply_text = get_field('after_you_apply_text');
$sub_content = get_field('sub_content');
$multiclass_team_link = get_field('multiclass_team_link');

?>
<style type="text/css">
    #approx_hours-error{
        display: none!important;
    }
</style>
<div class="appointment_form_main_dv">
    <div class="main_page_title">
        <h1 class="first"><?php _e('Apply to become a teacher at','astra-child'); ?> <span><?php _e('Multiclass','astra-child'); ?></span></h1>
        <h2 class="second"><?= get_the_title() ?></h2>
    </div>


    <div class="container">
        <div class="first_section_dv">
            <div class="center-text">
                <?= $main_content ?>
            </div>
            <div class="application_form_listing">
                <h5><?= $application_form_part_title ?></h5>
                <ol type="1">
                    <?php if(!empty($part_titles)){
                    foreach ($part_titles as $title) {  ?>
                        <li><?= $title['part_title_detail'] ?></li>
                    <?php } } ?>
                </ol>
            </div>
            <div class="application_form_listing">
                <h5><?= $this_is_what_happens_after_you_apply ?></h5>
                <ol type="1">
                    <?php if(!empty($after_you_apply_text)){
                    foreach ($after_you_apply_text as $title) {  ?>
                        <li><?= $title['content'] ?></li>
                    <?php } } ?>
                </ol>
            </div>
            <div class="center-text">
                <?= $sub_content ?>
            </div>
            <div class="center-text">           
                <div class=""> 
                    <a href="<?= $multiclass_team_link['url'] ?>"><?= $multiclass_team_link['title'] ?></a>
                </div>
                <button class="first_step_btn"><?php _e('Next','astra-child'); ?></button>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Form Start -->
        <div class="multisteps_form_main_dv">
            <form id="msform" class="multistep_form" method="POST" action="" enctype="multipart/form-data">
                <!-- progressbar -->
                <ul id="progressbar">
                    <li class="active" id="contact_detail"><?php _e('Contact Details','astra-child'); ?></li>
                    <li id="qualifications"><?php _e('Qualifications','astra-child'); ?></li>
                    <li id="possible_uses"><?php _e('Possible Uses','astra-child'); ?></li>
                    <li id="cv_and_certificates"><?php _e('CV and Certificates','astra-child'); ?></li>
                </ul>
                <!-- First Step Start -->
                <fieldset>
                    <div class="form-card form-card-1">
                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Full Name','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="first-last-name">
                                    <div class="form-group">
                                        <input type="text" name="first_name" class="first_name" id="first_name" placeholder="<?php _e('First Name','astra-child'); ?>"/>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="last_name" class="last_name" id="last_name" placeholder="<?php _e('Last Name','astra-child'); ?>"/>
                                    </div>
                                </div>
                                <span class="errors first_err"><?php _e('Bitte geben Sie Ihren Vornamen ein.','astra-child'); ?></span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Gender','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="radio-btn-main">
                                    <div class="radio-btn">
                                        <label for="female">
                                            <input type="radio" id="female" name="gender" value="Female">
                                            <span><?php _e('Female','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">
                                        <label for="masculine">
                                            <input type="radio" id="masculine" name="gender" value="Masculine">
                                            <span><?php _e('Masculine','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">
                                        <label for="miscellaneous">
                                            <input type="radio" id="miscellaneous" name="gender" value="Miscellaneous">
                                            <span><?php _e('Miscellaneous','astra-child'); ?></span>
                                        </label>
                                    </div>
                                </div>
                                <span class="errors gender_err"><?php _e('Bitte wählen Sie Ihr Geschlecht aus.','astra-child'); ?></span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Birth Date','astra-child'); ?></label>
                            <div class="form-group-inner">
                                <div class="birth-date">
                                    <div class="form-group">
                                        <select class="birth_date" id="birth_date" name="birth_date">
                                            <option value="">DD</option>
                                            <?php for ($i = 1; $i <= 31; $i++) { ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <select class="birth_month" id="birth_month" name="birth_month">
                                            <option value="">MM</option>
                                            <?php for ($i = 1; $i <= 12; $i++) { ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-group year-group">
                                        <select class="birth_year" id="birth_year" name="birth_year">
                                            <option value="">YYYY</option>
                                            <?php 
                                                $currentYear = date("Y"); 
                                                for ($i = $currentYear; $i >= 1960; $i--) { 
                                            ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Mobile Number','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="form-group mobile-group">
                                    <input type="tel" name="mobile_number" class="mobile_number" id="mobile_number" placeholder="<?php _e('Mobile Number','astra-child'); ?>">
                                </div>
                                <span class="errors mobile_number_err">Bitte geben Sie Ihren Handynamen ein.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('I have a smartphone and can be reached via WhatsApp.','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="radio-btn-main">
                                    <div class="radio-btn">
                                        <label for="smartphone_yes">
                                            <input type="radio" id="smartphone_yes" name="smartphone" value="Yes">
                                            <span><?php _e('Yes','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">
                                        <label for="smartphone_no">
                                            <input type="radio" id="smartphone_no" name="smartphone" value="No">
                                            <span><?php _e('No','astra-child'); ?></span>
                                        </label>
                                    </div>
                                </div>
                                <span class="errors smartphone_err">Bitte Option auswählen.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Email','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="form-group email-group">
                                    <input type="email" name="email" class="email" id="email" placeholder="<?php _e('Email','astra-child'); ?>">
                                </div>
                                <span class="errors email_err">Bitte geben Sie Ihre E-Mail-Adresse ein.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('How did you find out about us?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="form-group about-group">
                                    <select class="find_us" id="find_us" name="find_us">
                                        <option value=""><?php _e('Please Select','astra-child'); ?></option>
                                        <option value="ETH/UZH online Marktplatz"><?php _e('ETH/UZH online marketplace','astra-child'); ?></option>
                                        <option value="ETH/UZH Aushang"><?php _e('ETH/UZH notice','astra-child'); ?></option>
                                        <option value="PH Zürich "><?php _e('PH Zurich','astra-child'); ?></option>
                                        <option value="Linkedin"><?php _e('Linkedin','astra-child'); ?></option>
                                        <option value="Ronorp"><?php _e('Ronorp','astra-child'); ?></option>
                                        <option value="Schulstelle.ch"><?php _e('Schulstelle.ch','astra-child'); ?></option>
                                        <option value="studentjob.ch"><?php _e('studentjob.ch','astra-child'); ?></option>
                                        <option value="studentenjobs.ch"><?php _e('studentjobs.ch','astra-child'); ?></option>
                                        <option value="Freunde/Bekannte"><?php _e('Friends/acquaintances','astra-child'); ?></option>
                                        <option value="Andere"><?php _e('Other','astra-child'); ?></option>
                                    </select>
                                </div>
                                <span class="errors find_us_err">Bitte Option auswählen.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Data protection','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="checkbox-main">
                                    <label>
                                        <input type="checkbox" class="data_protect" id="data_protect" name="data_protect">
                                        <span class="txt">
                                            <?php _e('I hereby confirm that I have read the ','astra-child'); ?><a href="<?= site_url('data-protection-declaration'); ?>"><?php _e('data protection declaration','astra-child'); ?></a> <?php _e(' and agree to it.','astra-child'); ?>
                                        </span>
                                    </label>
                                </div>
                                <span class="errors data_protect_err">Bitte bestätigen Sie die Datenschutzerklärung.</span>
                            </div>
                        </div>
                    </div> 
                    <button class="back_first_step back-btn"><?php _e('Back','astra-child'); ?></button>
                    <input type="button" name="next" class="next action-button main_first_step" value="<?php _e('Next','astra-child'); ?>" />
                </fieldset>

                <!-- Second Step Start -->
                <fieldset>
                    <div class="form-card form-card-2">
                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('What is the last training you completed or what title do you hold? If you are still in training, please let us know which subjects you are taking and when you expect to graduate?','astra-child'); ?> <span>*</span></label>
                            <div class="form-group-inner">
                                <div class="form-group">
                                    <textarea class="last_training" id="last_training" name="last_training" placeholder="<?php _e('Enter Detail','astra-child'); ?>"></textarea>
                                </div>
                                <span class="errors last_training_err">Bitte fügen Sie Details hinzu.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Why do you want to work as a tutor?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="form-group">
                                    <textarea class="tutor_work" id="tutor_work" name="tutor_work" placeholder="<?php _e('Enter Detail','astra-child'); ?>"></textarea>
                                </div>
                                <span class="errors tutor_work_err">Bitte fügen Sie Details hinzu.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('What experience do you have as a tutor?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="form-group">
                                    <textarea class="tutor_experience" id="tutor_experience" name="tutor_experience" placeholder="<?php _e('Enter Detail','astra-child'); ?>"></textarea>
                                </div>
                                <span class="errors tutor_experience_err">Bitte fügen Sie Details hinzu.</span>
                            </div>    
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('What skills do you think a tutor should have?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="form-group">
                                    <textarea class="tutor_skills" id="tutor_skills" name="tutor_skills" placeholder="<?php _e('Enter Detail','astra-child'); ?>"></textarea>
                                </div>
                                <span class="errors tutor_skills_err">Bitte fügen Sie Details hinzu.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('What would be the most important tip you would give to aspiring tutors before their first lessons?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="form-group">
                                    <textarea class="before_first_lesson" id="before_first_lesson" name="before_first_lesson" placeholder="<?php _e('Enter Detail','astra-child'); ?>"></textarea>
                                </div>
                                <span class="errors before_first_lesson_err">Bitte fügen Sie Details hinzu.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('What is your moral view on the fact that wealthier families can “buy” a high school diploma for their child through private lessons, while comparable students without the financial means cannot?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="form-group">
                                    <textarea class="moral_view" id="moral_view" name="moral_view" placeholder="<?php _e('Enter Detail','astra-child'); ?>"></textarea>
                                </div>
                                <span class="errors moral_view_err">Bitte fügen Sie Details hinzu.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Approximately how many hours have you taught so far?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="form-group">
                                    <input type="number" name="approx_hours" class="approx_hours" id="approx_hours" placeholder="<?php _e('Approx hours','astra-child'); ?>">
                                </div>
                                <span class="errors approx_hours_err">Bitte fügen Sie Details hinzu.</span>
                            </div>
                        </div>
                    </div> 
                    <input type="button" name="previous" class="previous action-button-previous back-btn" value="<?php _e('Back','astra-child'); ?>" />
                    <input type="button" name="next" class="next action-button main_second_step" value="<?php _e('Next','astra-child'); ?>" />
                </fieldset>

                <!-- Third Step Start -->
                <fieldset>
                    <div class="form-card form-card-3">
                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('What date are you available?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="birth-date">
                                    <div class="form-group">
                                        <select class="available_date" id="available_date" name="available_date">
                                            <option value="">DD</option>
                                            <?php for ($i = 1; $i <= 31; $i++) { ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <select class="available_month" id="available_month" name="available_month">
                                            <option value="">MM</option>
                                            <?php for ($i = 1; $i <= 12; $i++) { ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="form-group year-group">
                                        <select class="available_year" id="available_year" name="available_year">
                                            <option value="">YYYY</option>
                                            <?php 
                                                $currentYear = date("Y");
                                                $nextYear = $currentYear + 1;
                                                for ($i = $currentYear; $i <= $nextYear; $i++) { 
                                            ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="date-icon">
                                        <input type="text" id="datepicker" style="display:none;">
                                        <button type="button" id="calendar-icon"><i class="icon icon-icon-20"></i></button>
                                    </div>
                                </div>
                                <span class="errors available_date_err">Bitte wählen Sie den verfügbaren Termin aus.</span>
                            </div>
                        </div>

                        <div class="f-group-main"> 
                            <label class="fieldlabels"><?php _e('How long do you expect to teach for?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="radio-btn-main">
                                    <div class="radio-btn">
                                        <label for="one_to_three_month">
                                            <input type="radio" id="one_to_three_month" name="how_long_teach" value="1 to 3 months">
                                            <span><?php _e('1 to 3 months','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">
                                        <label for="four_to_twelve_month">
                                            <input type="radio" id="four_to_twelve_month" name="how_long_teach" value="4 to 12 months">
                                            <span><?php _e('4 to 12 months','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">    
                                        <label for="more_than_twelve_month">
                                            <input type="radio" id="more_than_twelve_month" name="how_long_teach" value="more than 12 months">
                                            <span><?php _e('more than 12 months','astra-child'); ?></span>
                                        </label>
                                    </div>
                                </div>
                                <span class="errors how_long_teach_err">Bitte Option auswählen.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('If you chose option 1 or 2 in the last question, please briefly explain why.','astra-child'); ?></label>
                            <div class="form-group-inner">
                                <div class="form-group">
                                    <textarea class="explain_why" id="explain_why" name="explain_why" placeholder="<?php _e('Enter Detail','astra-child'); ?>"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Are you planning a stay abroad or another job? If yes, from when and for how long?','astra-child'); ?></label>
                            <div class="form-group-inner">
                                <div class="form-group">
                                    <textarea class="planning_to_stay" id="planning_to_stay" name="planning_to_stay" placeholder="<?php _e('Enter Detail','astra-child'); ?>"></textarea>
                                </div>
                            </div>    
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('How many hours per week do you want to teach?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="radio-btn-main">
                                    <div class="radio-btn">
                                        <label for="one_to_two_hours">    
                                            <input type="radio" id="one_to_two_hours" name="hour_per_week" value="1 to 2 hours">
                                            <span><?php _e('1 to 2 hours','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">
                                        <label for="three_to_four_hour">
                                            <input type="radio" id="three_to_four_hour" name="hour_per_week" value="3 to 4 hours">
                                            <span><?php _e('3 to 4 hours','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">
                                        <label for="five_to_eight_hours">
                                            <input type="radio" id="five_to_eight_hours" name="hour_per_week" value="5 to 8 hours">
                                            <span><?php _e('5 to 8 hours','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">
                                        <label for="eight_to_sixteen_hours">
                                            <input type="radio" id="eight_to_sixteen_hours" name="hour_per_week" value="8 to 16 hours">
                                            <span><?php _e('8 to 16 hours','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">
                                        <label for="more_than_sixteen_hours">
                                            <input type="radio" id="more_than_sixteen_hours" name="hour_per_week" value="more than 16 hours">
                                            <span><?php _e('more than 16 hours','astra-child'); ?></span>
                                        </label>
                                    </div>
                                </div>
                                <span class="errors hour_per_week_err">Bitte Option auswählen.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Which of the following course assignments would be basically/mostly feasible for you?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="checkbox-main">
                                    <label for="assignments1">
                                        <input type="checkbox" id="assignments1" name="assignments" value="Jun - to Feb: Wed afternoon">
                                        <span><?php _e('Jun - to Feb: Wed afternoon','astra-child'); ?></span>
                                    </label>
                                    <label for="assignments2">
                                        <input type="checkbox" id="assignments2" name="assignments" value="Jun - to Feb: Sat">
                                        <span><?php _e('Jun - to Feb: Sat','astra-child'); ?></span>
                                    </label>
                                    <label for="assignments3">
                                        <input type="checkbox" id="assignments3" name="assignments" value="Jun - to Feb: Sun">
                                        <?php $current_lang = function_exists('weglot_get_current_language') ? weglot_get_current_language() : '';
                                            if ( $current_lang === 'de' ) : ?>
                                            <span>Jun – bis Feb: So</span>
                                        <?php else : ?>
                                            <span><?php _e('Jun - to Feb: Sun','astra-child'); ?></span>
                                        <?php endif; ?>
                                    </label>
                                    <label for="assignments4">
                                        <input type="checkbox" id="assignments4" name="assignments" value="Summer holidays: Individual weeks">
                                        <span><?php _e('Summer holidays: Individual weeks','astra-child'); ?></span>
                                    </label>
                                    <label for="assignments5">
                                        <input type="checkbox" id="assignments5" name="assignments" value="Autumn holidays: Individual weeks">
                                        <span><?php _e('Autumn holidays: Individual weeks','astra-child'); ?></span>
                                    </label>
                                    <label for="assignments6">
                                        <input type="checkbox" id="assignments6" name="assignments" value="Christmas/New Year">
                                        <span><?php _e('Christmas/New Year','astra-child'); ?></span>
                                    </label>
                                    <label for="assignments7">
                                        <input type="checkbox" id="assignments7" name="assignments" value="Sports holidays February 12th - 16th, 2024 morning">
                                        <span><?php _e('Sports holidays February 12th - 16th, 2024 morning','astra-child'); ?></span>
                                    </label>
                                    <label for="assignments8">    
                                        <input type="checkbox" id="assignments8" name="assignments" value="Sports holidays February 12th - 16th, 2024 afternoon">
                                        <span><?php _e('Sports holidays February 12th - 16th, 2024 afternoon','astra-child'); ?></span>
                                    </label>
                                    <label for="assignments9">
                                        <input type="checkbox" id="assignments9" name="assignments" value="Sports holidays February 19th - 23rd, 2024 morning">
                                        <span><?php _e('Sports holidays February 19th - 23rd, 2024 morning','astra-child'); ?></span>
                                    </label>
                                    <label for="assignments10">
                                        <input type="checkbox" id="assignments10" name="assignments" value="Sports holidays February 19th - 23rd, 2024 afternoon">
                                        <span><?php _e('Sports holidays February 19th - 23rd, 2024 afternoon','astra-child'); ?></span>
                                    </label>
                                </div>

                                <span class="errors assignments_err">Bitte Option auswählen.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Which educational paths/stations are you familiar with?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">  
                            <div class="checkbox-main checkbox-main-flex">  
                                <div class="checkbox-btn">                
                                    <label for="bms">
                                        <input type="checkbox" id="bms" name="educational_paths" value="BMS (various variants)">
                                        <span><?php _e('BMS (various variants)','astra-child'); ?></span>
                                    </label>
                                </div>
                                <div class="checkbox-btn">
                                    <label for="passerelle">
                                        <input type="checkbox" id="passerelle" name="educational_paths" value="Passerelle">
                                        <span><?php _e('Passerelle','astra-child'); ?></span>
                                    </label>
                                </div>
                                <div class="checkbox-btn">
                                    <label for="langgymi">
                                        <input type="checkbox" id="langgymi" name="educational_paths" value="Gymnasium">
                                        <span><?php _e('Gymnasium','astra-child'); ?></span>
                                    </label>
                                </div>    
                                <!-- <div class="checkbox-btn">
                                    <label for="short_gym">
                                        <input type="checkbox" id="short_gym" name="educational_paths[]" value="Short gym">
                                        <span><?php //_e('Short gym','astra-child'); ?></span>
                                    </label>
                                </div> -->
                                <div class="checkbox-btn">
                                    <label for="ib">
                                        <input type="checkbox" id="ims" name="educational_paths" value="IMS">
                                        <span><?php _e('IMS','astra-child'); ?></span>
                                    </label>
                                </div>
                                <div class="checkbox-btn">
                                    <label for="sekundarschule">
                                        <input type="checkbox" id="sekundarschule" name="educational_paths" value="Sekundarschule">
                                        <span><?php _e('Sekundarschule','astra-child'); ?></span>
                                    </label>
                                </div>
                            </div>
                                <span class="errors educational_paths_err">Bitte Option auswählen.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('How good are your German skills?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">  
                                <div class="radio-btn-main">  
                                    <div class="radio-btn">
                                        <label for="minimal_knowledge">
                                            <input type="radio" id="minimal_knowledge" name="german_skills" value="Minimal knowledge">
                                            <span><?php _e('Minimal knowledge','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">
                                        <label for="basic_knowledge">
                                            <input type="radio" id="basic_knowledge" name="german_skills" value="Basic knowledge">
                                            <span><?php _e('Basic knowledge','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <div class="radio-btn">
                                        <label for="fluently">
                                            <input type="radio" id="fliessend" name="german_skills" value="Fliessend">
                                            <span><?php _e('Fliessend','astra-child'); ?></span>
                                        </label>
                                    </div>
                                    <!-- <div class="radio-btn">
                                        <label for="negotiable_gym">
                                            <input type="radio" id="negotiable_gym" name="german_skills" value="Negotiable gym">
                                            <span><?php //_e('Negotiable gym','astra-child'); ?></span>
                                        </label>
                                    </div> -->
                                    <div class="radio-btn">
                                        <label for="mother_tongue">
                                            <input type="radio" id="mother_tongue" name="german_skills" value="Mother tongue">
                                            <span><?php _e('Mother tongue','astra-child'); ?></span>
                                        </label>
                                    </div>
                                </div>
                                <span class="errors german_skills_err">Bitte Option auswählen.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Which subjects can you teach at primary level (1st - 6th grade)?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="table-group">
                                    <table>
                                        <tr>
                                            <th></th>
                                            <th><?php _e('Impossible','astra-child'); ?></th>
                                            <th><?php _e('Rather not','astra-child'); ?></th>
                                            <th><?php _e('Gut','astra-child'); ?></th>
                                            <th><?php _e('Very good','astra-child'); ?></th>
                                            <th><?php _e('Specialty','astra-child'); ?></th>
                                        </tr>
                                        <tr>
                                        <td><?php _e('German','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_primary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_primary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_primary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_primary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>    
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_primary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('English','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_primary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_primary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_primary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_primary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_primary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('French','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_primary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_primary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_primary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_primary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_primary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Mathematik','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_primary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_primary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_primary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_primary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_primary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <span class="errors primary_err">Bitte Option auswählen.</span>
                            </div>
                        </div>


                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Which subjects can you teach at secondary level I (7th - 9th grade)?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="table-group">
                                    <table>
                                        <tr>
                                            <th></th>
                                            <th><?php _e('Impossible','astra-child'); ?></th>
                                            <th><?php _e('Rather not','astra-child'); ?></th>
                                            <th><?php _e('Gut','astra-child'); ?></th>
                                            <th><?php _e('Very good','astra-child'); ?></th>
                                            <th><?php _e('Specialty','astra-child'); ?></th>
                                        </tr>
                                        <tr>
                                            <td><?php _e('German','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_secondary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_secondary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_secondary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_secondary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_secondary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('English','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_secondary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_secondary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_secondary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_secondary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_secondary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('French','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_secondary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_secondary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_secondary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_secondary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_secondary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Mathematik','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_secondary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_secondary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_secondary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_secondary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_secondary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Latein','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="latin_secondary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="latin_secondary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="latin_secondary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="latin_secondary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="latin_secondary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Biologie','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="organic_secondary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="organic_secondary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="organic_secondary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="organic_secondary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="organic_secondary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Chemistry','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="chemistry_secondary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="chemistry_secondary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="chemistry_secondary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="chemistry_secondary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="chemistry_secondary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Physics','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="physics_secondary" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="physics_secondary" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="physics_secondary" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="physics_secondary" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="physics_secondary" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <span class="errors secondary_error">Bitte Option auswählen.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Which subjects can you teach at the Matura level (10th - 12th school year), or apprenticeship/BMS or higher?','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="table-group">
                                    <table>
                                        <tr>
                                        <th></th>
                                        <th><?php _e('Impossible','astra-child'); ?></th>
                                        <th><?php _e('Rather not','astra-child'); ?></th>
                                        <th><?php _e('Gut','astra-child'); ?></th>
                                        <th><?php _e('Very good','astra-child'); ?></th>
                                        <th><?php _e('Specialty','astra-child'); ?></th>
                                        </tr>
                                        <tr>
                                            <td><?php _e('German','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="gernam_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('English','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="english_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('French','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="french_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Mathematik','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="maths_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Latein','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="latin_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="latin_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="latin_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="latin_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="latin_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Biologie','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="organic_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="organic_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="organic_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="organic_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="organic_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Chemistry','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="chemistry_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="chemistry_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="chemistry_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="chemistry_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="chemistry_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Story','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="story_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>        
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="story_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>        
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="story_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>         
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="story_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>         
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="story_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>         
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Geography','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="geography_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>                 
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="geography_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>                 
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="geography_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>                 
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="geography_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>          
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="geography_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>          
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Accounting','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="accounting_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>          
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="accounting_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>          
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="accounting_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>           
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="accounting_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>           
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="accounting_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>           
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><?php _e('Physics','astra-child'); ?></td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="physics_matura" value="Impossible">
                                                        <span></span>
                                                    </label>
                                                </div>         
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="physics_matura" value="Rather not">
                                                        <span></span>
                                                    </label>
                                                </div>         
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="physics_matura" value="Goes well">
                                                        <span></span>
                                                    </label>
                                                </div>         
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="physics_matura" value="Very good">
                                                        <span></span>
                                                    </label>
                                                </div>         
                                            </td>
                                            <td>
                                                <div class="radio-btn">
                                                    <label>
                                                        <input type="radio" name="physics_matura" value="Specialty">
                                                        <span></span>
                                                    </label>
                                                </div>         
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <span class="errors matura_error">Bitte Option auswählen.</span>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('What is your native language? Or do you have several? (Please differentiate between Swiss German and German)','astra-child'); ?></label>
                                <div class="form-group-inner">
                                <div class="form-group">
                                    <textarea class="native_language" id="native_language" name="native_language" placeholder="<?php _e('Enter Detail','astra-child'); ?>"></textarea>
                                </div>
                            </div>
                        </div>

                    </div> 
                    <input type="button" name="previous" class="previous action-button-previous back-btn" value="<?php _e('Back','astra-child'); ?>" />
                    <input type="button" name="next" class="next action-button main_third_step" value="<?php _e('Next','astra-child'); ?>"/>
                </fieldset>

                <!-- Fourth Step Start -->
                <fieldset>
                    <div class="form-card form-card-4">
                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Please upload your CV/CV including passport photo here (as a PDF).','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="file-upload">
                                    <label>
                                        <input type="file" name="resume" class="resume" id="resume">
                                        <span>
                                            <i class="icon icon-cloud-arrow-up"></i>
                                            <?php _e('Choose File','astra-child'); ?>
                                        </span>
                                    </label>
                                    <button class="remove-button" id="remove-resume-button"><i class="far fa-trash-can"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Please upload your high school/vocational school leaving certificate here.','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="file-upload">
                                    <label>
                                        <input type="file" name="leaving_certificate" class="leaving_certificate" id="leaving_certificate">
                                        <span>
                                            <i class="icon icon-cloud-arrow-up"></i>
                                            <?php _e('Choose File','astra-child'); ?>
                                        </span>
                                    </label>
                                    <button class="remove-button" id="remove-leaving-button"><i class="far fa-trash-can"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Please upload your most recent training certificates here (e.g. interim certificates with grades).','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="file-upload">
                                    <label>
                                        <input type="file" name="training_certificate" class="training_certificate" id="training_certificate">
                                        <span>
                                            <i class="icon icon-cloud-arrow-up"></i>
                                            <?php _e('Choose File','astra-child'); ?>
                                        </span>
                                    </label>
                                    <button class="remove-button" id="remove-training-button"><i class="far fa-trash-can"></i></button>
                                </div>    
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Please upload your most recent references here','astra-child'); ?><span>*</span></label>
                            <div class="form-group-inner">
                                <div class="file-upload">
                                    <label>
                                        <input type="file" name="references_doc" class="references_doc" id="references_doc">
                                        <span>
                                            <i class="icon icon-cloud-arrow-up"></i>
                                            <?php _e('Choose File','astra-child'); ?>
                                        </span>
                                    </label>    
                                    <button class="remove-button" id="remove-reference-button"><i class="far fa-trash-can"></i></button>
                                </div>    
                            </div>
                        </div>

                        <div class="f-group-main">
                            <label class="fieldlabels"><?php _e('Add other achievements','astra-child'); ?> <span>*</span></label>
                            <div class="form-group-inner">
                                <div id="fileInputsContainer"></div>
                                <button id="addFileInputButton">
                                    <i class="icon icon-add-more"></i>
                                    <?php _e('Add','astra-child'); ?>
                                </button>
                            </div>
                        </div>
                        
                    </div>
                    <input type="button" name="previous" class="previous action-button-previous" value="<?php _e('Back','astra-child'); ?>" />
                    <input type="submit" value="<?php _e('Send','astra-child'); ?>" /> 
                </fieldset>

                <!-- Last Thank you section -->
                <div class="thank_you_page">
                    <?php if(have_rows('thank_you_step_content')){
                        while (have_rows('thank_you_step_content')) {
                        the_row();
                        $title = get_sub_field('title');
                        $content = get_sub_field('content');
                        $button = get_sub_field('button');  ?>

                        <img src="<?= site_url() ?>/wp-content/uploads/2024/05/great-1.png">
                        <h3><?= $title ?></h3>
                            <?= $content ?>
                        <div class="go-home-btn">    
                            <a href="<?= $button['url'] ?>"><?= $button['title'] ?></a>
                        </div>
                        <?php }
                    } ?>
                </div>
            </form>
        </div>
        <!-- Form End -->
    </div>
 
</div>
<?php get_footer(); ?>