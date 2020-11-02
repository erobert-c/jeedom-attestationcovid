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
use setasign\Fpdi\PdfReader;
require_once __DIR__  . '/../../../../core/php/core.inc.php';
require_once __DIR__ . '/../../3rdparty/fpdf/fpdf.php';
require_once __DIR__ . '/../../3rdparty/fpdi/src/autoload.php';
require_once __DIR__ . '/../../3rdparty/phpqrcode/qrlib.php';

class attestationcovid extends eqLogic {
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
      public static function cronDaily() {
        log::add(self::_NAME, 'debug', 'Suppression des attestations');
        self::cleanUp();
      }

  private static function cleanUp() {
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


    /*     * *********************Méthodes d'instance************************* */

 // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert() {

    }

 // Fonction exécutée automatiquement après la création de l'équipement
    public function postInsert() {

    }

 // Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate() {
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

      if ($this->getConfiguration('autoSend') && empty($this->getConfiguration('sendCmd'))) {
        throw new Exception(__('La commande d\'envoi ne peut pas être vide si l\'envoi automatique est activé', __FILE__));
      }
    }

 // Fonction exécutée automatiquement après la mise à jour de l'équipement
    public function postUpdate() {

    }

 // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
    public function preSave() {

    }

 // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
    public function postSave() {
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
    }

 // Fonction exécutée automatiquement avant la suppression de l'équipement
    public function preRemove() {

    }

 // Fonction exécutée automatiquement après la suppression de l'équipement
    public function postRemove() {

    }

    /*     * **********************Getteur Setteur*************************** */
    private function initConfiguration() {
      $this->_firstname = $this->getConfiguration("firstname");
      $this->_lastname = $this->getConfiguration("lastname");
      $this->_birthdate = $this->getConfiguration("birthday");
      $this->_birthplace = $this->getConfiguration("birthplace");
      $this->_address = $this->getConfiguration("address");
      $this->_city = $this->getConfiguration("city");
      $this->_postalcode = $this->getConfiguration("postalcode");
    }

    public function generate($logicalId) {
      $date_day = date("d/m/Y");
      $time_day = date("H\hi");
      $this->initConfiguration();
      $motif = $logicalId;

      return $this->createPdf($date_day, $time_day, $motif);
    }

    private function createPdf($date_day, $time_day, $motif) {

      $pdf = new Fpdi();
      $pdf->AddPage();
      $pdf->setSourceFile(self::_RESOURCE_PATH . 'certificate.pdf');
      $pageId = $pdf->importPage(1);
      $size = $pdf->getTemplateSize($pageId);
      $pdf->useTemplate($pageId, 0, 0, $size['width'], $size['height'], true);
      $pdf->SetFont('Arial', '', '11');
      $pdf->SetTextColor(0,0,0);
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
      $pdf->SetXY(26, $yReasons[$motif]);
      $pdf->SetFont('Arial', '', '15');
      $pdf->Write(0, 'X');
      $pdf->SetFont('Arial', '', '11');

      // Date
      $pdf->SetXY(36, 234);
      $pdf->Write(0, $this->_city);
      $pdf->SetXY(31, 243);
      $pdf->Write(0, $date_day);
      $pdf->SetXY(88, 242);
      $pdf->Write(0, $time_day);

      $qrPath = self::_RESOURCE_PATH.'qr_'.$this->_firstname.'.png';
      $data = $this->generateQR($date_day, $time_day, $motif, $qrPath);
      $pdf->Image($qrPath, 140, 220, 43, 43, 'PNG');

      // second ldap_control_paged_result
      $pdf->AddPage();
      $pdf->Image($qrPath, 20, 20, 60, 60, 'PNG');

      // Save the file
      $pdf->Output('F', self::_RESOURCE_PATH . 'attestation_'.$this->_firstname.'.pdf', true);

      if ($this->getConfiguration('autoSend')) {
        $this->send($date_day, $time_day, $motif);
      }
      return $data;
    }

    private function generateQR($date_day, $time_day, $motif, $qrPath) {
      $data = "Cree le ".$date_day." a ".$time_day;
      $data .= ";\nNom: ".$this->_lastname;
      $data .= ";\nPrenom: ".$this->_firstname;
      $data .= ";\nNaissance: ".$this->_birthdate." a ".$this->_birthplace;
      $data .= ";\nAdresse: ".$this->_address." ".$this->_postalcode." ".$this->_city;
      $data .= ";\nSortie: ".$date_day." a ".$time_day;
      $data .= ";\nMotifs: ".$motif;
      log::add(self::_NAME, 'debug', 'Génération du QRCode avec les infos: '.$data);
      QRCode::png($data, $qrPath);
      log::add(self::_NAME, 'debug', 'QR code généré: '.$qrPath);
      return $data;
    }

    public function send($date_day, $time_day, $motifs) {
      $this->initConfiguration();
      $options = array();
      $path = self::_RESOURCE_PATH.'attestation_'.$this->_firstname.'.pdf';
      if (!file_exists($path)) {
        throw new Exception(__('Pas d\'attestation trouvée pour '.$this->_firstname.'. Générez une attestation avant d\'essayer d\'en envoyer une', __FILE__));
      }
      $options['files'] = array($path);
      log::add(self::_NAME, 'debug', 'Envoi du fichier '.$path);
      $cmd = cmd::byId(str_replace('#', '', $this->getConfiguration('sendCmd')));
      if (!is_object($cmd)) {
        log::add(self::_NAME, 'error', 'La commande d\'envoi n\'est pas correctement configurée');
        throw new Exception(__('La commande d\'envoi n\'est pas configurée', __FILE__));
      }
      if ($date_day == NULL) {
        log::add(self::_NAME, 'debug', 'Envoi de la dernière attestation générée pour '.$this->_firstname);
        $options['message'] = $this->_firstname.' voici la dernière attestation générée';
      } else {
        $options['message'] = $this->_firstname.' voici votre attestation du '
                              .$date_day.' a '.$time_day.'. Motifs: '.str_replace('_', '\_', $motifs);
      }
      try {
        $cmd->execCmd($options);
      } catch (Exception $e) {
        log::add('attestationcovid', 'error', __('Erreur lors de l\'envoi de l\'attestation : ', __FILE__) . $cmd->getHumanName() . ' => ' . log::exception($e));
        throw new Exception(__('Erreur lors de l\'envoi de l\'attestation', __FILE__));
      }
    }
}

class attestationcovidCmd extends cmd {
    /*     * *************************Attributs****************************** */


  // Exécution d'une commande
     public function execute($_options = array()) {
       $eqlogic = $this->getEqLogic(); //récupère l'éqlogic de la commande $this
       if ($this->getLogicalId() === 'sendLast') {
         $eqlogic->send(NULL, NULL, NULL);
       } else {
         $info = $eqlogic->generate($this->getLogicalId());
       }

       $eqlogic->checkAndUpdateCmd('attestation', $info);
     }

    /*     * **********************Getteur Setteur*************************** */
}
