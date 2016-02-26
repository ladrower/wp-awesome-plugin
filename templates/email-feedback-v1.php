<h1 style="color: #061602;font-family: Impact;letter-spacing: 2px;font-size: 22px; text-transform: uppercase;">Feedback from
    <a href="mailto:<?= \WPAwesomePlugin\OutputHelper::toSafeHtml($feedbackSender->email) ?>">
        <?= \WPAwesomePlugin\OutputHelper::toSafeHtml($feedbackSender->username) ?>
    </a> (<?= \WPAwesomePlugin\OutputHelper::toSafeHtml($feedbackSender->country) ?>)
</h1>
<p><?= nl2br(\WPAwesomePlugin\OutputHelper::toSafeHtml($feedbackSender->feedback)) ?></p>
