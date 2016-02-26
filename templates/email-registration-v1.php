<p><?= __('Hi there') ?></p>
<p><?= sprintf(__("Welcome to %s! Here's how to log in:"), get_option('blogname')) ?></p>
<p><?= wp_login_url() ?></p>
<p><?= sprintf(__('Username: %s'), $user_login) ?></p>
<p><?= sprintf(__('If you have any problems, please contact me at %s.'), get_option('admin_email')) ?></p>
<p><?= __('Adios!') ?></p>
