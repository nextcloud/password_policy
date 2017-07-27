<?php
echo $l->t('Hello %s,', [$_['user']]);
echo "<br/><br/>";

echo $l->t('Your password is about to expire on %s.', [$_['expireDay']]);
echo "<br/><br/>";
echo $l->t('Please login to your account and change your password:');
echo str_replace('{link}', $_['linkToPage'], '<br/><a href="{link}">{link}</a>');