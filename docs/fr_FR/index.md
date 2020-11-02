
# Plugin attestation covid

Ce plugin permet de générer une attestation de sortie dérogatoire en période de confinement. Pour le moment, un seul motif peut être sélectionné.

## Configuration du plugin
Après téléchargement du plugin, il vous suffit juste d’activer celui-ci, il n’y a aucune configuration supplémentaire à ce niveau.

## Configuration des équipements
Une fois le plugin activé, il est visible dans le menu "Plugins/Organisation".

Vous pouvez alors définir plusieurs équipements, correspondant à un template d'attestation.

### Onglet Equipement
Cet onglet comporte les informations de bases qui seront utilisées pour la génération de l'attestation. Toutes les coordonnées sont nécessaire pour pouvoir sauvegarder l'équipement. Ces informations sont celles de la personne pour qui l'attestation sera générée.

Sélectionnez la commande qui sera utilisée pour l'envoi de l'attestation, et si l'envoi doit se faire automatiquement à a génération. Si vous ne cochez pas cette case, il faudra envoyer l'attestation avec la commande 'Envoi';

### Onglet commandes
Les commandes sont automatiquement générée à la création de l'équipement et correspondent aux différents motifs de sortie:
* Travail: "Déplacements entre le domicile et le lieu d’exercice de l’activité professionnelle ou un établissement d’enseignement ou de formation, déplacements professionnels ne pouvant être différés, déplacements pour un concours ou un examen"
* Achats: "Déplacements pour effectuer des achats de fournitures nécessaires à l'activité professionnelle, des achats de première nécessité dans des établissements dont les activités demeurent autorisées, le retrait de commande et les livraisons à domicile"
* Santé: "Consultations, examens et soins ne pouvant être assurés à distance et l’achat de médicaments"
* Famille: "Déplacements pour motif familial impérieux, pour l'assistance aux personnes vulnérables et précaires ou la garde d'enfants"
* Handicap: "Déplacement des personnes en situation de handicap et leur accompagnant"
* SportsAnimaux: "Déplacements brefs, dans la limite d'une heure quotidienne et dans un rayon maximal d'un kilomètre autour du domicile, liés soit à l'activité physique individuelle des personnes, à l'exclusion de toute pratique sportive collective et de toute proximité avec d'autres personnes, soit à la promenade avec les seules personnes regroupées dans un même domicile, soit aux besoins des animaux de compagnie"
* Convocation: "Convocation judiciaire ou administrative et pour se rendre dans un service public"
* Missions: "Participation à des missions d'intérêt général sur demande de l'autorité administrative"
* Enfants: "Déplacement pour chercher les enfants à l’école et à l’occasion de leurs activités périscolaires"

En exécutant une des commandes, une attestation est générée, reprenant les coordonnées configurées et le motif sélectionné. Cette attestation est envoyée en utilisant la commande d'envoi configurée.

## FAQ
* Le plugin utilise t-il des API externes?
Non, la génération se fait entièrement en local. Un template de base est contenu dans les ressources du plugin et est utilisé pour générer l'attestation.
Cependant, le plugin s'appuie sur les librairies [FPDF](http://www.fpdf.org/), [FPDI](https://www.setasign.com/products/fpdi/downloads/) et [PHP QRCode](http://phpqrcode.sourceforge.net/).
* Peut-on générer une attestation avec plusieurs motifs de sortie?
Pas pour le moment.
