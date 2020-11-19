<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */

use setasign\Fpdi\Fpdi;

require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../3rdparty/fpdf/fpdf.php';
require_once __DIR__ . '/../../3rdparty/phpqrcode/qrlib.php';

class attestationcovid extends eqLogic
{
  /*     * *************************Attributs****************************** */
  const _NAME = 'attestationcovid';
  const _RESOURCE_PATH = __DIR__ . '/../../resources/';

  private $_firstname;
  private $_lastname;
  private $_birthdate;
  private $_birthplace;
  private $_address;
  private $_postalcode;
  private $_city;

  /*     * ***********************Methode static*************************** */
  public static function dependancy_install()
  {
    log::remove(__CLASS__ . '_update');
    return array('script' => dirname(__FILE__) . '/../../resources/install.sh', 'log' => log::getPathToLog(__CLASS__ . '_update'));
  }

  public static function dependancy_info()
  {
    $return = array();
    $return['log'] = __CLASS__ . '_update';
    $return['progress_file'] = '/tmp/dependency_attestationcovid_in_progress';
    $cmd = system::getCmdSudo() . '/bin/bash ' . dirname(__FILE__) . '/../../resources/install_check.sh';
    if (exec($cmd) == "ok") {
      $return['state'] = 'ok';
    } else {
      $return['state'] = 'nok';
    }
    return $return;
  }
  /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
     * - Nettoyage des attestation et QR code générés
    */
  public static function cronDaily()
  {
    log::add(self::_NAME, 'debug', 'Suppression des attestations');
    self::cleanUp();
  }

  private static function cleanUp()
  {
    $patternAttestation = 'attestation_*.pdf';
    $patternQR = 'qr_*.png';

    // Clean up des attestations
    foreach (glob(self::_RESOURCE_PATH . $patternAttestation) as $attestation) {
      unlink($attestation);
    }
    // Clean up des QR Codes
    log::add(self::_NAME, 'debug', 'Suppression des QR Codes');
    foreach (glob(self::_RESOURCE_PATH . $patternQR) as $qr) {
      unlink($qr);
    }
  }

  public static function goBackToDefault($_params)
  {
    $eqLogic = eqLogic::byId($_params['attestation_id']);
    if (!is_object($eqLogic)) {
      return;
    }
    $mode = $eqLogic->getConfiguration('mode') == 'mono' ? 'Motif unique' : 'Plusieurs motifs';
    log::add(self::_NAME, 'info', $eqLogic->getHumanName() . ' Retour à la configuration par défaut pour l\'envoi des attestations: ' . $mode);
    $eqLogic->checkAndUpdateCmd('overrideMode', 0);
  }


  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert()
  {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert()
  {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate()
  {
    $this->initConfiguration();
    if (empty($this->_firstname)) {
      throw new Exception(__('Le prénom ne peut pas être vide', __FILE__));
    }
    if (empty($this->_lastname)) {
      throw new Exception(__('Le nom de famille ne peut pas être vide', __FILE__));
    }
    if (empty($this->_birthdate)) {
      throw new Exception(__('La date de naissance ne peut pas être vide', __FILE__));
    }
    if (empty($this->_birthplace)) {
      throw new Exception(__('Le lieu de naissance ne peut pas être vide', __FILE__));
    }
    if (empty($this->_address)) {
      throw new Exception(__('L\'adresse ne peut pas être vide', __FILE__));
    }
    if (empty($this->_postalcode)) {
      throw new Exception(__('Le code postal ne peut pas être vide', __FILE__));
    }
    if (empty($this->_city)) {
      throw new Exception(__('La ville ne peut pas être vide', __FILE__));
    }
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate()
  {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave()
  {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave()
  {
    $info = $this->getCmd(null, 'attestation');
    if (!is_object($info)) {
      $info = new attestationcovidCmd();
      $info->setName(__('Attestation', __FILE__));
    }
    $info->setLogicalId('attestation');
    $info->setEqLogic_id($this->getId());
    $info->setType('info');
    $info->setSubType('string');
    $info->setOrder(1);
    $info->save();

    // Travail
    $travail = $this->getCmd(null, 'travail');
    if (!is_object($travail)) {
      $travail = new attestationcovidCmd();
      $travail->setName(__('Travail', __FILE__));
    }
    $travail->setEqLogic_id($this->getId());
    $travail->setLogicalId('travail');
    $travail->setType('action');
    $travail->setSubType('other');
    $travail->setOrder(2);
    $travail->save();

    // Achats
    $achats = $this->getCmd(null, 'achats');
    if (!is_object($achats)) {
      $achats = new attestationcovidCmd();
      $achats->setName(__('Achats', __FILE__));
    }
    $achats->setEqLogic_id($this->getId());
    $achats->setLogicalId('achats');
    $achats->setType('action');
    $achats->setSubType('other');
    $achats->setOrder(3);
    $achats->save();

    // sante
    $sante = $this->getCmd(null, 'sante');
    if (!is_object($sante)) {
      $sante = new attestationcovidCmd();
      $sante->setName(__('Santé', __FILE__));
    }
    $sante->setEqLogic_id($this->getId());
    $sante->setLogicalId('sante');
    $sante->setType('action');
    $sante->setSubType('other');
    $sante->setOrder(4);
    $sante->save();

    // famille
    $famille = $this->getCmd(null, 'famille');
    if (!is_object($famille)) {
      $famille = new attestationcovidCmd();
      $famille->setName(__('Famille', __FILE__));
    }
    $famille->setEqLogic_id($this->getId());
    $famille->setLogicalId('famille');
    $famille->setType('action');
    $famille->setSubType('other');
    $famille->setOrder(5);
    $famille->save();

    // handicap
    $handicap = $this->getCmd(null, 'handicap');
    if (!is_object($handicap)) {
      $handicap = new attestationcovidCmd();
      $handicap->setName(__('Handicap', __FILE__));
    }
    $handicap->setEqLogic_id($this->getId());
    $handicap->setLogicalId('handicap');
    $handicap->setType('action');
    $handicap->setSubType('other');
    $handicap->setOrder(6);
    $handicap->save();

    // sports_animaux
    $animaux = $this->getCmd(null, 'sport_animaux');
    if (!is_object($animaux)) {
      $animaux = new attestationcovidCmd();
      $animaux->setName(__('Sports/Animaux', __FILE__));
    }
    $animaux->setEqLogic_id($this->getId());
    $animaux->setLogicalId('sport_animaux');
    $animaux->setType('action');
    $animaux->setSubType('other');
    $animaux->setOrder(7);
    $animaux->save();

    // convocation
    $convocation = $this->getCmd(null, 'convocation');
    if (!is_object($convocation)) {
      $convocation = new attestationcovidCmd();
      $convocation->setName(__('Convocation', __FILE__));
    }
    $convocation->setEqLogic_id($this->getId());
    $convocation->setLogicalId('convocation');
    $convocation->setType('action');
    $convocation->setSubType('other');
    $convocation->setOrder(8);
    $convocation->save();

    // missions
    $missions = $this->getCmd(null, 'missions');
    if (!is_object($missions)) {
      $missions = new attestationcovidCmd();
      $missions->setName(__('Missions', __FILE__));
    }
    $missions->setEqLogic_id($this->getId());
    $missions->setLogicalId('missions');
    $missions->setType('action');
    $missions->setSubType('other');
    $missions->setOrder(9);
    $missions->save();

    // enfants
    $enfants = $this->getCmd(null, 'enfants');
    if (!is_object($enfants)) {
      $enfants = new attestationcovidCmd();
      $enfants->setName(__('Enfants', __FILE__));
    }
    $enfants->setEqLogic_id($this->getId());
    $enfants->setLogicalId('enfants');
    $enfants->setType('action');
    $enfants->setSubType('other');
    $enfants->setOrder(10);
    $enfants->save();

    // Envoi
    $send = $this->getCmd(null, 'sendLast');
    if (!is_object($send)) {
      $send = new attestationcovidCmd();
      $send->setName(__('Envoi', __FILE__));
    }
    $send->setEqLogic_id($this->getId());
    $send->setLogicalId('sendLast');
    $send->setType('action');
    $send->setSubType('other');
    $send->setOrder(11);
    $send->save();

    // Génération, sans envoi
    $generate = $this->getCmd(null, 'generate');
    if (!is_object($generate)) {
      $generate = new attestationcovidCmd();
      $generate->setName(__('Générer', __FILE__));
    }
    $generate->setEqLogic_id($this->getId());
    $generate->setLogicalId('generate');
    $generate->setType('action');
    $generate->setSubType('other');
    $generate->setOrder(12);
    $generate->save();

    // Liste de motifs
    $motifs = $this->getCmd(null, 'motifs');
    if (!is_object($motifs)) {
      $motifs = new attestationcovidCmd();
      $motifs->setName(__('Motifs', __FILE__));
    }
    $motifs->setEqLogic_id($this->getId());
    $motifs->setLogicalId('motifs');
    $motifs->setIsVisible(0);
    $motifs->setType('info');
    $motifs->setSubType('string');
    $motifs->setOrder(13);
    $motifs->save();

    // Changement temporaire mode
    $switchMode = $this->getCmd(null, 'switchMode');
    if (!is_object($switchMode)) {
      $switchMode = new attestationcovidCmd();
      $switchMode->setName(__('Changer mode', __FILE__));
    }
    $switchMode->setEqLogic_id($this->getId());
    $switchMode->setLogicalId('switchMode');
    $switchMode->setIsVisible(0);
    $switchMode->setType('action');
    $switchMode->setSubType('other');
    $switchMode->setOrder(14);
    $switchMode->save();

    // Override mode 
    $overrideMode = $this->getCmd(null, 'overrideMode');
    if (!is_object($overrideMode)) {
      $overrideMode = new attestationcovidCmd();
      $overrideMode->setName(__('Override mode', __FILE__));
    }
    $overrideMode->setEqLogic_id($this->getId());
    $overrideMode->setLogicalId('overrideMode');
    $overrideMode->setIsVisible(0);
    $overrideMode->setType('info');
    $overrideMode->setSubType('binary');
    $overrideMode->setOrder(15);
    $overrideMode->save();
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove()
  {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove()
  {
  }

  /*     * **********************Getteur Setteur*************************** */
  private function initConfiguration()
  {
    $this->_firstname = $this->getConfiguration("firstname");
    $this->_lastname = $this->getConfiguration("lastname");
    $this->_birthdate = $this->getConfiguration("birthday");
    $this->_birthplace = $this->getConfiguration("birthplace");
    $this->_address = $this->getConfiguration("address");
    $this->_city = $this->getConfiguration("city");
    $this->_postalcode = $this->getConfiguration("postalcode");
  }

  public function setMotif($motif)
  {
    $currentMotifs = $this->getCmd(null, 'motifs')->execCmd();
    if (!empty($currentMotifs)) {
      $motifs = explode(',', $currentMotifs);
    } else {
      $motifs = array();
    }
    if (!in_array($motif, $motifs)) {
      array_push($motifs, $motif);
    }
    return implode(',', $motifs);
  }

  public function generateOnReasonChange()
  {
    return $this->getConfiguration('mode') == 'mono' && ($this->getCmd(null, 'overrideMode')->execCmd() == 0 || empty($this->getCmd(null, 'overrideMode')->execCmd()));
  }

  public function switchMode()
  {
    $override = $this->getCmd(null, 'overrideMode')->execCmd();
    if (empty($override)) {
      $override = 0;
    }
    $this->checkAndUpdateCmd('overrideMode', !$override);

    // Schedule the back to normal automatically
    $delay = intval($this->getConfiguration('delay'));
    if (empty($delay)) {
      $delay = 3;
    }
    log::add(self::_NAME, 'info', 'Le mode par défaut sera réactivé automatiquement après ' . $delay . ' minute(s), ou après la génération de l\'attestation');
    $backToDefault = strtotime('now') + $delay * 60;
    $cron = new cron();
    $cron->setClass('attestationcovid');
    $cron->setFunction('goBackToDefault');
    $cron->setOption(array('attestation_id' => intval($this->getId())));
    $cron->setLastRun(date('Y-m-d H:i:s'));
    $cron->setOnce(1);
    $cron->setSchedule(cron::convertDateToCron($backToDefault));
    $cron->save();
  }

  public function cleanCronOverride()
  {
    $crons = cron::searchClassAndFunction(self::_NAME, 'goBackToDefault', '"attestation_id":' . $this->getId());
    if (is_array($crons) && count($crons) > 0) {
      foreach ($crons as $cron) {
        if ($cron->getState() != 'run') {
          $cron->remove();
        }
      }
    }
  }

  public function generate()
  {
    $motifs = $this->getCmd(null, 'motifs')->execCmd();
    if (empty($motifs)) {
      throw new Exception(__('Aucun motif sélectionné, l\'attestation ne peut pas être générée', __FILE__));
    }
    $date_day = date("d/m/Y");
    $time_day = date("H\hi");
    $this->initConfiguration();

    // A la generation, on clear le cron
    $this->cleanCronOverride();
    self::goBackToDefault(array('attestation_id' => $this->getId()));

    return $this->fillPdf($date_day, $time_day);
  }

  private function fillPdf($date_day, $time_day)
  {
    $motifs = explode(',', $this->getCmd(null, 'motifs')->execCmd());

    $fdfPath = self::_RESOURCE_PATH . 'form_' . $this->_firstname . '.fdf';
    $fdf = file_get_contents(self::_RESOURCE_PATH . 'form.fdf');
    $fdf = str_replace('NOMPRENOM', $this->_firstname . ' ' . $this->_lastname, $fdf);
    $fdf = str_replace('DATENAISSANCE', $this->_birthdate, $fdf);
    $fdf = str_replace('LIEUNAISSANCE', $this->_birthplace, $fdf);
    $fdf = str_replace('ADRESSE', $this->_address . ' ' . $this->_postalcode . ' ' . $this->_city, $fdf);
    $fdf = str_replace('MOTIF-1', in_array('travail', $motifs) ? 'Oui' : 'Off', $fdf);
    $fdf = str_replace('MOTIF-2', in_array('achats', $motifs) ? 'Oui' : 'Off', $fdf);
    $fdf = str_replace('MOTIF-3', in_array('sante', $motifs) ? 'Oui' : 'Off', $fdf);
    $fdf = str_replace('MOTIF-4', in_array('famille', $motifs) ? 'Oui' : 'Off', $fdf);
    $fdf = str_replace('MOTIF-5', in_array('handicap', $motifs) ? 'Oui' : 'Off', $fdf);
    $fdf = str_replace('MOTIF-6', in_array('sport_animaux', $motifs) ? 'Oui' : 'Off', $fdf);
    $fdf = str_replace('MOTIF-7', in_array('convocation', $motifs) ? 'Oui' : 'Off', $fdf);
    $fdf = str_replace('MOTIF-8', in_array('missions', $motifs) ? 'Oui' : 'Off', $fdf);
    $fdf = str_replace('MOTIF-9', in_array('enfants', $motifs) ? 'Oui' : 'Off', $fdf);
    $fdf = str_replace('DATE', $this->_city, $fdf);
    $fdf = str_replace('LIEU', $date_day, $fdf);
    $fdf = str_replace('HEURE', $time_day, $fdf);
    log::add(__CLASS__, 'debug', $fdf);
    file_put_contents($fdfPath, $fdf);

    // QR PDFs
    $qr1Path = self::_RESOURCE_PATH . 'qr1_' . $this->_firstname . '.pdf';
    $qrPath = self::_RESOURCE_PATH . 'qr_' . $this->_firstname . '.png';
    $data = $this->generateQR($date_day, $time_day, $motifs, $qrPath);
    $pdfQr1 = new FPDF('P', 'mm', 'A4');
    $pdfQr1->AddPage();
    $pdfQr1->Image($qrPath, 140, 254, 39, 39, 'PNG');
    $pdfQr1->Output('F', $qr1Path);
    log::add(self::_NAME, 'debug', __('QR code première page générée', __FILE__));

    // second QR Code
    $qr2Path = self::_RESOURCE_PATH . 'qr2_' . $this->_firstname . '.pdf';
    $pdfQr2 = new FPDF('P', 'mm', 'A4');
    $pdfQr2->AddPage();
    $pdfQr2->Image($qrPath, 20, 20, 65, 65, 'PNG');
    $pdfQr2->Output('F', $qr2Path);
    log::add(self::_NAME, 'debug', __('QR code seconde page générée', __FILE__));

    // Merge form and the first QR code
    $certifPath = self::_RESOURCE_PATH . 'certificate_new.pdf';
    $page1TempPath = self::_RESOURCE_PATH . 'page1_' . $this->_firstname . '.pdf';
    $checkMerge = shell_exec('pdftk ' . $certifPath . ' stamp ' . $qr1Path . ' output ' . $page1TempPath);
    if ($checkMerge != 0) {
      throw new Exception(__('Erreur au moment de l\'ajout du premier QR code à l\'attestation.', __FILE__));
    }
    log::add(self::_NAME, 'debug', __('QR code intégrée à la première page avec le formulaire rempli', __FILE__));

    // Add second page with bigger QR code
    $tmpForm = self::_RESOURCE_PATH . 'form_' . $this->_firstname . '.pdf';
    $checkMerge = shell_exec('pdftk ' . $page1TempPath . ' ' . $qr2Path . ' cat output ' . $tmpForm);
    if ($checkMerge != 0) {
      throw new Exception(__('Erreur au moment de l\'ajout du second QR code à l\'attestation.', __FILE__));
    }
    log::add(self::_NAME, 'debug', __('Attestation sans formulaire complètement générée', __FILE__));

    $attestationPath = self::_RESOURCE_PATH . 'attestation_' . $this->_firstname . '.pdf';
    $checkFill = shell_exec('pdftk ' . $tmpForm . ' fill_form ' . $fdfPath . ' output ' . $attestationPath);
    if ($checkFill != 0) {
      throw new Exception(__('Erreur lors du remplissage du formulaire.', __FILE__));
    }
    log::add(self::_NAME, 'debug', __('Attestation complètement générée', __FILE__));

    unlink($qr1Path);
    unlink($qr2Path);
    unlink($page1TempPath);
    unlink($tmpForm);
    unlink($fdfPath);

    if ($this->getConfiguration('autoSend')) {
      $this->send($date_day, $time_day);
    }
    $this->checkAndUpdateCmd('motifs', '');
    return $data;
  }

  private function generateQR($date_day, $time_day, $motifs, $qrPath)
  {
    $data = "Cree le " . $date_day . " a " . $time_day;
    $data .= ";\nNom: " . $this->_lastname;
    $data .= ";\nPrenom: " . $this->_firstname;
    $data .= ";\nNaissance: " . $this->_birthdate . " a " . $this->_birthplace;
    $data .= ";\nAdresse: " . $this->_address . " " . $this->_postalcode . " " . $this->_city;
    $data .= ";\nSortie: " . $date_day . " a " . $time_day;
    $data .= ";\nMotifs: " . implode(',', $motifs);
    log::add(self::_NAME, 'debug', 'Génération du QRCode avec les infos: ' . $data);
    QRCode::png($data, $qrPath);
    log::add(self::_NAME, 'debug', 'QR code généré: ' . $qrPath);
    return $data;
  }

  public function send($date_day, $time_day)
  {
    $this->initConfiguration();
    $options = array();
    $path = self::_RESOURCE_PATH . 'attestation_' . $this->_firstname . '.pdf';
    if (!file_exists($path)) {
      throw new Exception(__('Pas d\'attestation trouvée pour ' . $this->_firstname . '. Générez une attestation avant d\'essayer d\'en envoyer une', __FILE__));
    }
    $options['files'] = array($path);
    log::add(self::_NAME, 'debug', 'Envoi du fichier ' . $path);
    $cmd = cmd::byId(str_replace('#', '', $this->getConfiguration('sendCmd')));
    if (!is_object($cmd)) {
      log::add(self::_NAME, 'error', 'La commande d\'envoi n\'est pas correctement configurée');
      throw new Exception(__('La commande d\'envoi n\'est pas configurée', __FILE__));
    }
    $motifs = $this->getCmd(null, 'motifs')->execCmd();;
    $options['message'] = $this->_firstname . ' voici votre attestation du '
      . $date_day . ' a ' . $time_day . '. Motifs: ' . str_replace('_', '\_', explode(',', $motifs));

    try {
      $cmd->execCmd($options);
    } catch (Exception $e) {
      log::add('attestationcovid', 'error', __('Erreur lors de l\'envoi de l\'attestation : ', __FILE__) . $cmd->getHumanName() . ' => ' . log::exception($e));
      throw new Exception(__('Erreur lors de l\'envoi de l\'attestation', __FILE__));
    }
  }
}

class attestationcovidCmd extends cmd
{
  /*     * *************************Attributs****************************** */


  // Exécution d'une commande
  public function execute($_options = array())
  {
    $eqlogic = $this->getEqLogic(); //récupère l'éqlogic de la commande $this
    switch ($this->getLogicalId()) {
      case 'sendLast':
        $eqlogic->send(null, null, null);
        break;
      case 'generate':
        $info = $eqlogic->generate();
        $eqlogic->checkAndUpdateCmd('attestation', $info);
        break;
      case 'switchMode':
        $eqlogic->switchMode();
        break;
      default:
        $info = $eqlogic->setMotif($this->getLogicalId());
        $eqlogic->checkAndUpdateCmd('motifs', $info);
        if ($eqlogic->generateOnReasonChange()) {
          $info = $eqlogic->generate();
          $eqlogic->checkAndUpdateCmd('attestation', $info);
        }

        break;
    }
  }

  /*     * **********************Getteur Setteur*************************** */
}
