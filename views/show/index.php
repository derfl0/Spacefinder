<? if ($maxpower): ?>
<table style="width: 100%; table-layout: fixed;" class="spaceship">
    <thead>
    <tr>
        <th style="width: 40px;"></th>
        <th><?= _('Montag') ?></th>
        <th><?= _('Dienstag') ?></th>
        <th><?= _('Mittwoch') ?></th>
        <th><?= _('Donnerstag') ?></th>
        <th><?= _('Freitag') ?></th>
        <th><?= _('Samstag') ?></th>
        <th><?= _('Sonntag') ?></th>
    </tr>
    </thead>
    <tbody>
    <? for ($i = 6; $i <= 24; $i++): ?>
        <tr>
            <td style="text-align: right"><?= $i ?>:00</td>
            <? for ($j = 1; $j <= 7; $j++): ?>
                <td style="<?= $power[$j][$i]? "background-color: rgb(".(round(255 / $maxpower * $power[$j][$i])).", ".(round(255 - 255 / $maxpower * $power[$j][$i]))." , 70)" : '' ?>">
                    <?= $power[$j][$i] ?>
                </td>
            <? endfor; ?>
        </tr>
    <? endfor; ?>
    </tbody>
</table>
<? else: ?>
<p>
    <?= _('Keine Einträge gefunden') ?>
</p>
<? endif; ?>

<style>
    table.spaceship th, table.spaceship td {
        border: 1px solid lightgrey;
        text-align: center;
    }
    table.spaceship {
        border-collapse: collapse;
    }
</style>

<script>
    $(document).ready(function() {
        $('input[name="from"]').datepicker();
    });
</script>