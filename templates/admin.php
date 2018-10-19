<h1>Dynamic Password</h1>
<h3>
    Which pages do you want to set a dynamic password?
</h3>

<form method="POST" style="max-width: 500px;">
    <table class="form-table">
        <tbody>
           <tr>
                <td>
                    Select a Page to Protect
                </td>
                <td>
                    <select name="page-id">
                        <option>
                            Select a page
                        </option>
                        <?php
                            $pages = get_pages();
                            $pagesid = [];
                            foreach ($pages as $page) {
                                // var_dump($page);
                                $pagesid[$page->ID] = $page->post_title;
                                ?>
                                    <option value="<? echo $page->ID; ?>">
                                        <? echo $page->post_title; ?>
                                    </option>
                                <?
                            }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                 <td>
                    Change Password Every
                 </td>
                 <td>
                    <select name="hours">
                         <option value="hourly" >
                            hourly
                         </option>
                        <option value="twicedaily" selected="selected">
                            twice daily
                        </option>
                        <option value="daily">
                            daily
                        </option>
                    </select>
                 </td>
             </tr>
            <tr>
                <td>
                    <input type="hidden" name="action" value="create" />
                    <input type="hidden" name="page" value="dynamic_password_protect" />
                    <input type="submit" value="Set Dynamic Password" />
                <td>
            </tr>
        </tbody>
    </table>
</form>

<h3>
    Select a Page to Remove the Dynamic Password
</h3>

<form method="POST" style="max-width: 500px;">
    <table class="form-table">
        <tbody>
           <tr>
                <td>
                    Current Schedules
                </td>
                <td>
                    <select name="page-id">
                        <option>
                            Select a page
                        </option>
                        <? 
                            $events = get_cron_events();
                            foreach ( $events as $id => $event ) {
                                if ($event->hook == "wpse_change_pass_event") {
                                    
                                    ?>
                                        <option value="<? echo $event->sig; ?>">
                                            <? echo $pagesid[$event->args[0]]; ?>
                                        </option>
                                    <?
                                }
                            }

                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <input type="hidden" name="action" value="remove" />
                    <input type="hidden" name="page" value="dynamic_password_protect" />
                    <input type="submit" value="Remove Dynamic Password" />
                <td>
            </tr>
        </tbody>
    </table>
</form>
