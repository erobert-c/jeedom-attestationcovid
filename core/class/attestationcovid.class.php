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
require_once __DIR__ . '/../../3rdparty/fpdi/src/autoload.php';
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
    log::add(self::_NAME, 'info', $eqLogic->getHumanName().' Retour à la configuration par défaut pour l\'envoi des attestations: '.$mode);
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
    log::add(self::_NAME, 'info', 'Le mode par défaut sera réactivé automatiquement après '.$delay.' minute(s), ou après la génération de l\'attestation');
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

    return $this->createPdf($date_day, $time_day);
  }

  private function createPdf($date_day, $time_day)
  {
    $pdf = new Fpdi();
    $pdf->AddPage();
    $pdf->setSourceFile(self::_RESOURCE_PATH . 'certificate.pdf');
    $pageId = $pdf->importPage(1);
    $size = $pdf->getTemplateSize($pageId);
    $pdf->useTemplate($pageId, 0, 0, $size['width'], $size['height'], true);
    $pdf->SetFont('Arial', '', '11');
    $pdf->SetTextColor(0, 0, 0);
    $yReasons = array(
      "travail" => 92,
      "achats" => 108,
      "sante" => 128,
      "famille" => 142,
      "handicap" => 156,
      "sport_animaux" => 170,
      "convocation" => 192,
      "missions" => 206,
      "enfants" => 222
    );

    //Nom/prenom:
    $pdf->SetXY(40, 50);
    //first parameter defines the line height
    $pdf->Write(0, $this->_firstname . ' ' . $this->_lastname);

    // Naissance:
    $pdf->SetXY(40, 58);
    $pdf->Write(0, $this->_birthdate);
    $pdf->SetXY(104, 58);
    $pdf->Write(0, $this->_birthplace);

    // Adresse
    $pdf->SetXY(45, 66);
    $pdf->Write(0, $this->_address . ' ' . $this->_postalcode . ' ' . $this->_city);

    // $reason
    $motifs = explode(',', $this->getCmd(null, 'motifs')->execCmd());
    foreach ($motifs as $motif) {
      $pdf->SetXY(26, $yReasons[$motif]);
      $pdf->SetFont('Arial', '', '15');
      $pdf->Write(0, 'X');
      $pdf->SetFont('Arial', '', '11');
    }

    // Date
    $pdf->SetXY(36, 234);
    $pdf->Write(0, $this->_city);
    $pdf->SetXY(31, 243);
    $pdf->Write(0, $date_day);
    $pdf->SetXY(88, 242);
    $pdf->Write(0, $time_day);

    $qrPath = self::_RESOURCE_PATH . 'qr_' . $this->_firstname . '.png';
    $data = $this->generateQR($date_day, $time_day, $motifs, $qrPath);
    $pdf->Image($qrPath, 140, 220, 43, 43, 'PNG');

    // second ldap_control_paged_result
    $pdf->AddPage();
    $pdf->Image($qrPath, 20, 20, 60, 60, 'PNG');

    // Save the file
    $pdf->Output('F', self::_RESOURCE_PATH . 'attestation_' . $this->_firstname . '.pdf', true);

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
