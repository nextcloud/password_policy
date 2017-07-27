<?php
echo $l->t('Hello %s,', [$_['user']]);
echo "\n\n";

echo $l->t('Your password is about to expire on %s.', [$_['expireDay']]);
echo "\n\n";
echo $l->t('Please login to your account and change your password:');
echo "\n";
echo $_['linkToPage'];