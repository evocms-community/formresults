<h1>
    <i class="fa fa-list"></i><?= $_lang['formresults.module_title'] ?>
</h1>

<?php if (!empty($form)): ?>
    <div id="actions">
        <div class="btn-group">
            <?php if (!empty($columns)): ?>
                <?php if (!empty($results)): ?>
                    <?php if ($canExport): ?>
                        <a id="Button4" class="btn btn-primary" href="<?= $moduleUrl ?>&type=<?= $form['alias'] ?>&action=export" target="_blank">
                            <i class="fa fa-download"></i><span><?= $_lang['formresults.xls_export'] ?></span>
                        </a>
                    <?php endif; ?>

                    <a class="btn btn-danger" href="#" onclick="return removeResults(event);">
                        <i class="fa fa-trash"></i><span><?= $_lang['formresults.delete_all'] ?></span>
                    </a>
                <?php endif; ?>

                <a id="Button5" class="btn btn-secondary" href="<?= $moduleUrl ?>">
                    <i class="fa fa-arrow-up"></i><span><?= $_lang['formresults.back'] ?></span>
                </a>
            <?php else: ?>
                <a id="Button5" class="btn btn-secondary" href="<?= $moduleUrl ?>&type=<?= $form['alias'] ?>">
                    <i class="fa fa-arrow-up"></i><span><?= $_lang['formresults.back'] ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="sectionBody" id="formsPane">
    <div class="tab-pane" id="documentPane">
        <script type="text/javascript">
            var tpFormResults = new WebFXTabPane(document.getElementById('documentPane'), false);
        </script>
