<div class="wrap">
    <?php if(empty($id)): ?>
        <h1><?php echo __("Add Schedule",WP_OTO_POSTER) ?></h1>
    <?php else: ?>
        <h1><?php echo __("Edit Schedule",WP_OTO_POSTER) ?></h1>
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
                    <?php echo __('Title') ?><br>
                    <input type="text" name="title" size="50" value="<?php if(!empty($post_title)){echo $post_title;}?>" required>
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo __('Url/Image') ?><br>
                    <input id="url" type="url" name="url" size="50" value="<?php if(!empty($post_url)){echo $post_url;}?>">
                </td>
            </tr>
             <tr>
                <td>
                    <?php echo __('Url #2') ?><br>
                    <input type="url" name="url2" size="50" value="<?php if(!empty($post_url2)){echo $post_url2;}?>">
                </td>
            </tr>
            <tr>
                <td>
                    <?php echo __('Message') ?><br>
                    <textarea name="message" rows="4" cols="50"><?php if(!empty($post_message)){echo $post_message;}?></textarea>
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