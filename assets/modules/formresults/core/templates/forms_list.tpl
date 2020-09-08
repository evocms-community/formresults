<div class="tab-page" id="tab_main">
    <h2 class="tab">
        <?= $_lang['formresults.module_title'] ?>
    </h2>

    <script type="text/javascript">
        tpFormResults.addTabPage(document.getElementById('tab_main'));
    </script>

    <div class="row">
        <div class="table-responsive">
            <table class="table data">
                <thead>
                    <tr>
                        <td><?= $_lang['formresults.form_name'] ?></td>
                        <td><?= $_lang['formresults.results_total'] ?></td>
                        <td><?= $_lang['formresults.last_result'] ?></td>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($forms as $form): ?>
                        <tr>
                            <td><a href="<?= $moduleUrl ?>&type=<?= $form['alias'] ?>"><?= htmlspecialchars($form['caption']) ?></td>
                            <td><?= $form['results_total'] ?></td>
                            <td><?= $form['last_result'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
