<div class="wrap">
    <?php if(empty($id)): ?>
        <h1><?php echo __("Add Random",WP_OTO_POSTER) ?></h1>
    <?php else: ?>
        <h1><?php echo __("Edit Random",WP_OTO_POSTER) ?></h1>
    <?php endif; ?>
    <form method="post">
        <?php if(!empty($errors)): ?>
            <div style="background:red;padding:16px">
                <?php foreach($errors as $error): ?>
                    <?php echo $error; ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if(!empty($success)): ?>
            <div style="background:green;padding:16px">
                <?php echo __('Saved',WP_OTO_POSTER); ?>
            </div>
        <?php endif; ?>

        <?php wp_nonce_field( 'wp_oto_poster_nonce' ); ?>   
        <input type="hidden" name="id" value="<?php if(!empty($id)){echo $id;}?>">
        
        <table>
            <tr>
                <td><?php echo __('Year',WP_OTO_POSTER); ?></td>
                <td><?php echo __('Month',WP_OTO_POSTER); ?></td>
                <td><?php echo __('Day / th',WP_OTO_POSTER); ?></td>
                <td><?php echo __('Day of the week',WP_OTO_POSTER); ?></td>
                <td><?php echo __('Hour',WP_OTO_POSTER); ?></td>
                <td><?php echo __('Min',WP_OTO_POSTER); ?></td>
            </tr>
            <tr>
                <td>
                    <select name="year">
                        <?php foreach($years as $year): ?>
                            <option value="<?php echo  $year ?>" <?php if($post_year == $year){ echo 'selected';}?>><?php echo $year ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="month">
                        <?php foreach($months as $month): ?>
                            <option value="<?php echo  $month ?>" <?php if($post_month == $month){ echo 'selected';}?>><?php echo $month ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="day">
                        <?php foreach($days as $day): ?>
                            <option value="<?php echo  $day ?>" <?php if($post_day == $day){ echo 'selected';}?>><?php echo $day ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="dayoftheweek">
                        <?php foreach($daysOfTheWeek as $k => $day): ?>
                            <option value="<?php echo  $k ?>" <?php if($post_dayoftheweek == $k){ echo 'selected';}?>><?php echo $day ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="hour">
                        <?php foreach($hours as $k => $hour): ?>
                            <option value="<?php echo  $k ?>" <?php if($post_hour == $k){ echo 'selected';}?>><?php echo $hour ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="min">
                        <?php foreach($mins as $k => $min): ?>
                            <option value="<?php echo  $k ?>" <?php if($post_min == $k){ echo 'selected';}?>><?php echo $min ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <table> 
            <tr>
                <td><input type="checkbox" name="is_facebook" value="1" <?php if(!empty($post_is_facebook)){echo 'checked';}?>> <?php echo __('Facebook',WP_OTO_POSTER) ?></td>
                <td><input type="checkbox" name="is_twitter" value="1" <?php if(!empty($post_is_twitter)){echo 'checked';}?>> <?php echo __('Twitter',WP_OTO_POSTER) ?></td>
                <td><input type="checkbox" name="is_instagram" value="1" <?php if(!empty($post_is_instagram)){echo 'checked';}?>> <?php echo __('Instagram',WP_OTO_POSTER) ?></td>
                <td><input type="checkbox" name="is_pinterest" value="1" <?php if(!empty($post_is_pinterest)){echo 'checked';}?>> <?php echo __('Pinterest',WP_OTO_POSTER) ?></td>
            </tr>      
        </table>    
        <table>
            <tr>
                <td>
                    <?php echo __('Post Type',WP_OTO_POSTER) ?><br>
                    <input type="radio" id="post_type_post" name="post_type" value="0" <?php if($post_type ==0){echo 'checked';}?>>
                    <label for="post_type_post"><?php echo __('Post') ?></label>
                    <input type="radio" id="post_type_image" name="post_type" value="1" <?php if($post_type ==1){echo 'checked';}?>>
                    <label for="post_type_image"><?php echo __('Image') ?></label>
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo __('Title') ?><br>
                    <input type="text" name="title" size="50" value="<?php if(!empty($post_title)){echo $post_title;}?>" required>
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo __('Message before content',WP_OTO_POSTER) ?><br>
                    <textarea name="message0" rows="4" cols="50"><?php if(!empty($post_message0)){echo $post_message0;}?></textarea>
                </td>
            </tr>
            <tr>
                <td><input type="checkbox" name="post_is_content" value="1" <?php if(!empty($post_is_content)){echo 'checked';}?>> <?php echo __('Show content',WP_OTO_POSTER) ?></td>
            </tr>
            <tr>
                <td>
                    <?php echo __('Message after content',WP_OTO_POSTER) ?><br>
                    <textarea name="message" rows="4" cols="50"><?php if(!empty($post_message)){echo $post_message;}?></textarea>
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo __('Categories') ?><br>
                    <input type="text" name="categories" size="50" value="<?php if(!empty($post_categories)){echo $post_categories;}?>">
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo __('Tags') ?><br>
                    <input type="text" name="tags" size="50" value="<?php if(!empty($post_tags)){echo $post_tags;}?>">
                </td>
            </tr>
        </table>
        <p class="submit">
            <?php if(empty($id)): ?>
                <input id="submit" class="button button-primary" type="submit" name="submit" value="<?php echo __('Add'); ?>">
            <?php else: ?>
                <input id="submit" class="button button-primary" type="submit" name="submit" value="<?php echo __('Edit'); ?>">
            <?php endif; ?>
        </p>
    </form>
</div>