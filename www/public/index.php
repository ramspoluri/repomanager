<!DOCTYPE html>
<html>
<?php
require_once('../models/Autoloader.php');
Autoloader::load();
include_once('../includes/head.inc.php');
require_once('../functions/repo.functions.php');
?>

<body>
<?php include_once('../includes/header.inc.php'); ?>

<article>
<!-- On charge la section de droite avant celle de gauche car celle-ci peut mettre plus de temps à charger (si bcp de repos) -->
<section class="mainSectionRight">
    <?php if (Common::isadmin()) { ?>
        <!-- GERER L'AFFICHAGE -->
        <?php include_once('../includes/display.inc.php'); ?>

        <!-- AJOUTER UN NOUVEAU REPO/SECTION -->
        <?php include_once('../templates/forms/op-form-new.inc.php'); ?> 

        <!-- EXECUTER DES OPERATIONS -->
        <?php include_once('../includes/operation.inc.php'); ?> 

        <!-- GERER LES GROUPES -->
        <?php include_once('../includes/manage-groups.inc.php'); ?>

        <!-- GERER LES SOURCES -->
        <?php include_once('../includes/manage-sources.inc.php'); ?>
    <?php } ?>

    <section class="right">
        <h3>PROPRIÉTÉS</h3>

        <div class="div-generic-gray server-properties-container">
            <?php
            /**
             *  Récupération du total des repos actifs et repos archivés
             */
            $repo = new Repo();
            $totalRepos = $repo->countActive();
            $totalReposArchived = $repo->countArchived(); ?>

            <div>
                <div class="server-properties">
                    <div class="server-properties-count">
                        <span>
                            <?php echo $totalRepos ?>
                        </span>
                    </div>
                    <div>
                        <span>Repos</span>
                    </div>
                </div>

                <div class="server-properties">
                    <div class="server-properties-count">
                        <span>
                            <?php echo $totalReposArchived ?>
                        </span>
                    </div>
                    <div>
                        <span>Repos archivés</span>
                    </div>
                </div>
            </div>

            <div class="donut-chart-container">
                <?php
                $diskTotalSpace = disk_total_space(REPOS_DIR);
                $diskFreeSpace = disk_free_space(REPOS_DIR);
                $diskUsedSpace = $diskTotalSpace - $diskFreeSpace;
                $diskTotalSpace = $diskTotalSpace / 1073741824;
                $diskUsedSpace = $diskUsedSpace / 1073741824;
                /**
                 *  Formattage des données pour avoir un résultat sans virgule et un résultat en pourcentage
                 */
                $diskFreeSpace = round(100 - (($diskUsedSpace / $diskTotalSpace) * 100));
                $diskFreeSpacePercent = $diskFreeSpace;
                $diskUsedSpace = round(100 - ($diskFreeSpace));
                $diskUsedSpacePercent = round(100 - ($diskFreeSpace));?>

                <p class="donut-legend-title lowopacity"><?=REPOS_DIR?></p>
                <span class="donut-legend-content"><?=$diskUsedSpace.'%'?></span>

                <?php
                $donutChartName = 'donut-chart';
                include(ROOT.'/includes/index-donut.inc.php');
                ?>
            </div>
        </div>

        <?php
        if (AUTOMATISATION_ENABLED == "yes") {
            $plan = new Planification();
            $lastPlan = $plan->listLast();
            $nextPlan = $plan->listNext();

            if (!empty($lastPlan or !empty($nextPlan))) { ?>
                <div class="div-generic-gray">
                <?php
                if (!empty($lastPlan)) {
                    if ($lastPlan['Status'] == 'done') {
                        $planStatus = 'OK';
                        $borderColor = '-green';
                    } else {
                        $planStatus = 'Erreur';
                        $borderColor = '-red';
                    } ?>
                    <div class="server-properties">
                        <div class="server-properties-count<?=$borderColor?>">
                            <span>
                                <?php echo $planStatus; ?>
                            </span>
                        </div>

                        <div>
                            <span><a href="planifications.php">Dernière planification (<?=DateTime::createFromFormat('Y-m-d', $lastPlan['Date'])->format('d-m-Y').' à '.$lastPlan['Time']?>)</a></span>
                        </div>
                    </div>
<?php           }
                    
                if (!empty($nextPlan)) { 
                    /**
                     *  Calcul du nombre de jours restants avant la prochaine planification
                     */
                    $date_now = new DateTime(DATE_YMD);
                    $date_plan = new DateTime($nextPlan['Date']);
                    $time_now = new DateTime(date('H:i'));
                    $time_plan = new DateTime($nextPlan['Time']);

                    $days_left = $date_plan->diff($date_now);
                    $time_left = $time_plan->diff($time_now); ?>
                    <div class="server-properties">
                        <div class="server-properties-count">
                            <span>
                                <?php
                                /**
                                 *  Si le nombre de jours restants = 0 (jour même) alors on affiche le nombre d'heures restantes
                                 */
                                if ($days_left->days == 0) {
                                    /**
                                     *  Si le nombre d'heures restantes = 0 alors on affiche les minutes restantes
                                     */
                                    if ($time_left->format('%h') == 0) {
                                        echo $time_left->format('%im');
                                    } else {
                                        echo $time_left->format('%hh%im');
                                    }
                                } else {
                                    echo $days_left->days.'j'; 
                                } ?>
                            </span>
                        </div>
                        <div>
                            <span><a href="planifications.php">Prochaine planification (<?=DateTime::createFromFormat('Y-m-d', $nextPlan['Date'])->format('d-m-Y').' à '.$nextPlan['Time']?>)</a></span>
                        </div>
                    </div>
<?php           }
            }
        } ?>
    </section>
</section>

<!-- section 'conteneur' principal englobant toutes les sections de gauche -->
<!-- On charge la section de gauche après celle de droite car elle peut mettre plus de temps à charger (si bcp de repos) -->
<section class="mainSectionLeft">
    <section class="left reposList">
        <!-- REPOS ACTIFS -->
        <?php include_once('../includes/repos-list-container.inc.php'); ?>
    </section>
    <section class="left reposList">
        <!-- REPOS ARCHIVÉS-->
        <?php include_once('../includes/repos-archive-list-container.inc.php'); ?>
    </section>
</section>
</article>

<?php include_once('../includes/footer.inc.php'); ?>

</body>
</html>