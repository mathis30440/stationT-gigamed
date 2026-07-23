<?php

namespace App\Gigamed\Service;

use App\Gigamed\Configuration\ConfigurationMail;
use App\Gigamed\Configuration\ConfigurationSite;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailService
{
    private function creerMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = ConfigurationMail::$host;
        $mail->SMTPAuth   = true;
        $mail->Username   = ConfigurationMail::$username;
        $mail->Password   = ConfigurationMail::$password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = ConfigurationMail::$port;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom(ConfigurationMail::$from, ConfigurationMail::$fromName);
        return $mail;
    }

    private function logoHtml(PHPMailer $mail): string
    {
        $logoPath = __DIR__ . '/../../ressources/image/Station T.jpg';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_stationt', 'Station T.jpg');
            return '<img src="cid:logo_stationt" alt="Station T" style="max-width:200px; margin-bottom:24px;">';
        }
        return '<strong>Station T</strong>';
    }

    public function envoyerMailNouveauBesoin(string $email, string $titreBesoin, string $filiere, string $nomEntreprise): void
    {
        $mail = $this->creerMailer();
        $mail->addAddress($email);
        $logoHtml = $this->logoHtml($mail);

        $mail->isHTML(true);
        $mail->Subject = '[Station T] Nouveau besoin soumis — ' . $titreBesoin;

        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Nouveau besoin soumis</h2>
                <p>Un nouveau besoin vient d'être déposé sur la plateforme Station T.</p>
                <table style='width:100%; border-collapse:collapse; margin:20px 0;'>
                    <tr>
                        <td style='padding:8px 12px; background:#f3f4f6; font-weight:600; width:35%;'>Entreprise</td>
                        <td style='padding:8px 12px; border-bottom:1px solid #e5e7eb;'>" . htmlspecialchars($nomEntreprise) . "</td>
                    </tr>
                    <tr>
                        <td style='padding:8px 12px; background:#f3f4f6; font-weight:600;'>Titre</td>
                        <td style='padding:8px 12px; border-bottom:1px solid #e5e7eb;'>" . htmlspecialchars($titreBesoin) . "</td>
                    </tr>
                    <tr>
                        <td style='padding:8px 12px; background:#f3f4f6; font-weight:600;'>Filière</td>
                        <td style='padding:8px 12px;'>" . htmlspecialchars($filiere) . "</td>
                    </tr>
                </table>
                <p>Connectez-vous à l'interface d'administration pour examiner ce besoin.</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";

        $mail->send();
    }

    public function envoyerMailIncomplet(string $email, string $prenom, string $nomProjet, string $message): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = ConfigurationMail::$host;
        $mail->SMTPAuth   = true;
        $mail->Username   = ConfigurationMail::$username;
        $mail->Password   = ConfigurationMail::$password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = ConfigurationMail::$port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(ConfigurationMail::$from, ConfigurationMail::$fromName);
        $mail->addAddress($email);

        $logoPath = __DIR__ . '/../../ressources/image/Station T.jpg';

        $mail->isHTML(true);
        $mail->Subject = 'Dossier incomplet — ' . $nomProjet . ' — Station T';

        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_stationt', 'Station T.jpg');
            $logoHtml = '<img src="cid:logo_stationt" alt="Station T" style="max-width:200px; margin-bottom:24px;">';
        } else {
            $logoHtml = '<strong>Station T</strong>';
        }

        $messageHtml = nl2br(htmlspecialchars($message));

        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Votre dossier est incomplet</h2>
                <p>Bonjour {$prenom},</p>
                <p>Votre candidature <strong>{$nomProjet}</strong> a été examinée par notre équipe. Des éléments sont manquants ou à compléter :</p>
                <div style='background:#fffbeb; border-left:4px solid #f59e0b; padding:16px 20px; border-radius:6px; margin:24px 0;'>
                    {$messageHtml}
                </div>
                <p>Merci de nous faire parvenir les éléments demandés dans les meilleurs délais afin que votre dossier puisse être instruit.</p>
                <p>Pour toute question, contactez-nous à <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a>.</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";

        $mail->send();
    }

    public function envoyerMailSuppressionCompte(string $email, string $prenom, string $raison): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = ConfigurationMail::$host;
        $mail->SMTPAuth   = true;
        $mail->Username   = ConfigurationMail::$username;
        $mail->Password   = ConfigurationMail::$password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = ConfigurationMail::$port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(ConfigurationMail::$from, ConfigurationMail::$fromName);
        $mail->addAddress($email);

        $logoPath = __DIR__ . '/../../ressources/image/Station T.jpg';
        $mail->isHTML(true);
        $mail->Subject = 'Votre compte Station T a été supprimé';

        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_stationt', 'Station T.jpg');
            $logoHtml = '<img src="cid:logo_stationt" alt="Station T" style="max-width:200px; margin-bottom:24px;">';
        } else {
            $logoHtml = '<strong>Station T</strong>';
        }

        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Votre compte a été supprimé</h2>
                <p>Bonjour {$prenom},</p>
                <p>Votre compte sur la plateforme <strong>Station T</strong> a été supprimé par l'équipe d'administration.</p>
                <div style='background:#fef2f2; border-left:4px solid #dc2626; padding:16px 20px; border-radius:6px; margin:24px 0;'>
                    " . nl2br(htmlspecialchars($raison)) . "
                </div>
                <p>Pour toute question : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a> — 06 59 71 38 57</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";

        $mail->send();
    }

    public function envoyerMailModificationCompte(string $email, string $prenom): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = ConfigurationMail::$host;
        $mail->SMTPAuth   = true;
        $mail->Username   = ConfigurationMail::$username;
        $mail->Password   = ConfigurationMail::$password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = ConfigurationMail::$port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(ConfigurationMail::$from, ConfigurationMail::$fromName);
        $mail->addAddress($email);

        $logoPath = __DIR__ . '/../../ressources/image/Station T.jpg';
        $mail->isHTML(true);
        $mail->Subject = 'Votre compte Station T a été modifié';

        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_stationt', 'Station T.jpg');
            $logoHtml = '<img src="cid:logo_stationt" alt="Station T" style="max-width:200px; margin-bottom:24px;">';
        } else {
            $logoHtml = '<strong>Station T</strong>';
        }

        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Vos informations ont été mises à jour</h2>
                <p>Bonjour {$prenom},</p>
                <p>Les informations de votre compte sur la plateforme <strong>Station T</strong> ont été modifiées par l'équipe d'administration.</p>
                <p>Si vous n'êtes pas à l'origine de cette modification ou si vous avez des questions, contactez-nous : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a></p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";

        $mail->send();
    }

    public function envoyerMailCreationCompte(string $email, string $prenom, string $token): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = ConfigurationMail::$host;
        $mail->SMTPAuth   = true;
        $mail->Username   = ConfigurationMail::$username;
        $mail->Password   = ConfigurationMail::$password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = ConfigurationMail::$port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(ConfigurationMail::$from, ConfigurationMail::$fromName);
        $mail->addAddress($email);

        $lien = ConfigurationSite::$baseUrl . '/choisir-mot-de-passe/' . $token;
        $logoPath = __DIR__ . '/../../ressources/image/Station T.jpg';
        $mail->isHTML(true);
        $mail->Subject = 'Votre compte Station T a été créé';

        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_stationt', 'Station T.jpg');
            $logoHtml = '<img src="cid:logo_stationt" alt="Station T" style="max-width:200px; margin-bottom:24px;">';
        } else {
            $logoHtml = '<strong>Station T</strong>';
        }

        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Votre compte a été créé</h2>
                <p>Bonjour {$prenom},</p>
                <p>Un compte vous a été créé sur la plateforme <strong>Station T</strong>.</p>
                <p>Pour activer votre accès, veuillez choisir votre mot de passe en cliquant sur le bouton ci-dessous :</p>
                <p style='margin:32px 0;'>
                    <a href='{$lien}' style='background:#0077cc; color:#fff; padding:12px 24px; border-radius:6px; text-decoration:none; font-weight:bold;'>
                        Choisir mon mot de passe
                    </a>
                </p>
                <p style='color:#666; font-size:13px;'>Ou copiez ce lien dans votre navigateur :<br>{$lien}</p>
                <p style='color:#999; font-size:12px; margin-top:16px;'>Ce lien est à usage unique. Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";

        $mail->send();
    }

    public function envoyerMailRefus(string $email, string $prenom, string $nomProjet, string $motif): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = ConfigurationMail::$host;
        $mail->SMTPAuth   = true;
        $mail->Username   = ConfigurationMail::$username;
        $mail->Password   = ConfigurationMail::$password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = ConfigurationMail::$port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(ConfigurationMail::$from, ConfigurationMail::$fromName);
        $mail->addAddress($email);

        $logoPath = __DIR__ . '/../../ressources/image/Station T.jpg';
        $mail->isHTML(true);
        $mail->Subject = 'Résultat de votre candidature — Station T';

        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_stationt', 'Station T.jpg');
            $logoHtml = '<img src="cid:logo_stationt" alt="Station T" style="max-width:200px; margin-bottom:24px;">';
        } else {
            $logoHtml = '<strong>Station T</strong>';
        }

        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Résultat de votre candidature</h2>
                <p>Bonjour {$prenom},</p>
                <p>Nous avons bien examiné votre candidature <strong>" . htmlspecialchars($nomProjet) . "</strong>.</p>
                <p>Après étude de votre dossier, nous sommes au regret de vous informer que votre candidature n'a pas pu être retenue à ce stade.</p>
                <div style='background:#fef2f2; border-left:4px solid #dc2626; padding:16px 20px; border-radius:6px; margin:24px 0;'>
                    " . nl2br(htmlspecialchars($motif)) . "
                </div>
                <p>Nous vous encourageons à nous contacter pour obtenir des précisions et, le cas échéant, à vous présenter lors d'un prochain appel à manifestation d'intérêt.</p>
                <p>Pour toute question : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a> — 06 59 71 38 57</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";

        $mail->send();
    }

    public function envoyerMailEligible(string $email, string $prenom, string $nomProjet): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = ConfigurationMail::$host;
        $mail->SMTPAuth   = true;
        $mail->Username   = ConfigurationMail::$username;
        $mail->Password   = ConfigurationMail::$password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = ConfigurationMail::$port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(ConfigurationMail::$from, ConfigurationMail::$fromName);
        $mail->addAddress($email);

        $logoPath = __DIR__ . '/../../ressources/image/Station T.jpg';
        $mail->isHTML(true);
        $mail->Subject = 'Votre candidature est éligible — Station T';

        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_stationt', 'Station T.jpg');
            $logoHtml = '<img src="cid:logo_stationt" alt="Station T" style="max-width:200px; margin-bottom:24px;">';
        } else {
            $logoHtml = '<strong>Station T</strong>';
        }

        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Votre candidature est éligible ✓</h2>
                <p>Bonjour {$prenom},</p>
                <p>Nous avons le plaisir de vous informer que votre candidature <strong>" . htmlspecialchars($nomProjet) . "</strong> a été déclarée <strong>éligible</strong>.</p>
                <p>Votre dossier va désormais entrer en phase d'instruction. Vous serez contacté prochainement par l'équipe Station T pour la suite du processus.</p>
                <p>Pour toute question : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a> — 06 59 71 38 57</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";

        $mail->send();
    }

    public function envoyerMailSelection(string $email, string $prenom, string $nomProjet): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = ConfigurationMail::$host;
        $mail->SMTPAuth   = true;
        $mail->Username   = ConfigurationMail::$username;
        $mail->Password   = ConfigurationMail::$password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = ConfigurationMail::$port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(ConfigurationMail::$from, ConfigurationMail::$fromName);
        $mail->addAddress($email);

        $logoPath = __DIR__ . '/../../ressources/image/Station T.jpg';
        $mail->isHTML(true);
        $mail->Subject = 'Félicitations — Votre candidature est sélectionnée — Station T';

        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_stationt', 'Station T.jpg');
            $logoHtml = '<img src="cid:logo_stationt" alt="Station T" style="max-width:200px; margin-bottom:24px;">';
        } else {
            $logoHtml = '<strong>Station T</strong>';
        }

        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#065f46;'>Félicitations — Votre candidature est sélectionnée !</h2>
                <p>Bonjour {$prenom},</p>
                <p>Nous avons le plaisir de vous informer que votre candidature <strong>" . htmlspecialchars($nomProjet) . "</strong> a été <strong>sélectionnée</strong> par l'équipe Station T.</p>
                <p>Vous serez contacté très prochainement pour organiser les prochaines étapes de l'expérimentation.</p>
                <p>Pour toute question : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a> — 06 59 71 38 57</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";

        $mail->send();
    }

    public function envoyerMailBesoinRecu(string $email, string $prenom, string $titreBesoin): void
    {
        $mail = $this->creerMailer();
        $mail->addAddress($email);
        $logoHtml = $this->logoHtml($mail);

        $mail->isHTML(true);
        $mail->Subject = 'Votre besoin a bien été reçu — Station T';
        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Besoin bien reçu</h2>
                <p>Bonjour {$prenom},</p>
                <p>Nous avons bien reçu votre besoin <strong>" . htmlspecialchars($titreBesoin) . "</strong>.</p>
                <p>Notre équipe va l'examiner et vous contactera prochainement.</p>
                <p>Pour toute question : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a> — 06 59 71 38 57</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";
        $mail->send();
    }

    public function envoyerMailBesoinRefuse(string $email, string $prenom, string $titreBesoin, string $raison): void
    {
        $mail = $this->creerMailer();
        $mail->addAddress($email);
        $logoHtml = $this->logoHtml($mail);

        $mail->isHTML(true);
        $mail->Subject = 'Votre besoin n\'a pas été retenu — Station T';
        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Résultat de l'analyse de votre besoin</h2>
                <p>Bonjour {$prenom},</p>
                <p>Nous avons examiné votre besoin <strong>" . htmlspecialchars($titreBesoin) . "</strong> et ne sommes pas en mesure de lui donner suite à ce stade.</p>
                <div style='background:#fef2f2; border-left:4px solid #dc2626; padding:16px 20px; border-radius:6px; margin:24px 0;'>
                    " . nl2br(htmlspecialchars($raison)) . "
                </div>
                <p>N'hésitez pas à nous contacter si vous souhaitez en discuter ou soumettre un nouveau besoin ultérieurement.</p>
                <p>Pour toute question : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a> — 06 59 71 38 57</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";
        $mail->send();
    }

    public function envoyerMailBesoinTransformeChallenge(string $email, string $prenom, string $titreBesoin): void
    {
        $mail = $this->creerMailer();
        $mail->addAddress($email);
        $logoHtml = $this->logoHtml($mail);

        $mail->isHTML(true);
        $mail->Subject = 'Votre besoin a été transformé en challenge — Station T';
        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Votre besoin devient un challenge !</h2>
                <p>Bonjour {$prenom},</p>
                <p>Nous avons le plaisir de vous informer que votre besoin <strong>" . htmlspecialchars($titreBesoin) . "</strong> a été transformé en <strong>challenge</strong> par l'équipe Station T.</p>
                <p>Un challenge a été créé à partir de votre besoin. Des startups pourront désormais y répondre en proposant leurs solutions.</p>
                <p>L'équipe Station T vous contactera prochainement pour vous tenir informé des candidatures reçues.</p>
                <p>Pour toute question : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a> — 06 59 71 38 57</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";
        $mail->send();
    }

    public function envoyerMailBesoinTransformeAmi(string $email, string $prenom, string $titreBesoin): void
    {
        $mail = $this->creerMailer();
        $mail->addAddress($email);
        $logoHtml = $this->logoHtml($mail);

        $mail->isHTML(true);
        $mail->Subject = 'Votre besoin a été transformé en AMI — Station T';
        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Votre besoin devient un AMI !</h2>
                <p>Bonjour {$prenom},</p>
                <p>Nous avons le plaisir de vous informer que votre besoin <strong>" . htmlspecialchars($titreBesoin) . "</strong> a été transformé en <strong>Appel à Manifestation d'Intérêt (AMI)</strong> par l'équipe Station T.</p>
                <p>Un AMI a été ouvert à partir de votre besoin. Des startups pourront y candidater et proposer leurs solutions innovantes.</p>
                <p>L'équipe Station T vous contactera prochainement pour vous tenir informé de l'avancement.</p>
                <p>Pour toute question : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a> — 06 59 71 38 57</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";
        $mail->send();
    }

    public function envoyerMailRelance(string $email, string $prenom, string $nomProjet, int $idCandidature): void
    {
        $mail = $this->creerMailer();
        $mail->addAddress($email);
        $logoHtml = $this->logoHtml($mail);

        $lien = ConfigurationSite::$baseUrl . '/partenaire/candidature/' . $idCandidature;

        $mail->isHTML(true);
        $mail->Subject = 'Rappel — Notation en attente — Station T';
        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Rappel : votre notation est attendue</h2>
                <p>Bonjour {$prenom},</p>
                <p>Vous avez été affecté à l'évaluation de la candidature <strong>" . htmlspecialchars($nomProjet) . "</strong>, mais nous n'avons pas encore reçu votre notation.</p>
                <p>Merci de bien vouloir saisir vos notes dès que possible en cliquant sur le bouton ci-dessous :</p>
                <p style='margin:32px 0;'>
                    <a href='{$lien}' style='background:#d97706; color:#fff; padding:12px 24px; border-radius:6px; text-decoration:none; font-weight:bold;'>
                        Accéder à la candidature
                    </a>
                </p>
                <p style='color:#666; font-size:13px;'>Ou copiez ce lien dans votre navigateur :<br>{$lien}</p>
                <p>Pour toute question : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a> — 06 59 71 38 57</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";
        $mail->send();
    }

    public function envoyerMailAffectation(string $email, string $prenom, string $nomProjet, int $idCandidature): void
    {
        $mail = $this->creerMailer();
        $mail->addAddress($email);
        $logoHtml = $this->logoHtml($mail);

        $lien = ConfigurationSite::$baseUrl . '/partenaire/candidature/' . $idCandidature;

        $mail->isHTML(true);
        $mail->Subject = 'Nouvelle candidature à évaluer — Station T';
        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Vous avez été affecté à une candidature</h2>
                <p>Bonjour {$prenom},</p>
                <p>L'équipe Station T vous a affecté à l'évaluation de la candidature <strong>" . htmlspecialchars($nomProjet) . "</strong>.</p>
                <p>Vous pouvez dès à présent consulter le dossier et saisir vos notes en cliquant sur le bouton ci-dessous :</p>
                <p style='margin:32px 0;'>
                    <a href='{$lien}' style='background:#0077cc; color:#fff; padding:12px 24px; border-radius:6px; text-decoration:none; font-weight:bold;'>
                        Accéder à la candidature
                    </a>
                </p>
                <p style='color:#666; font-size:13px;'>Ou copiez ce lien dans votre navigateur :<br>{$lien}</p>
                <p>Pour toute question : <a href='mailto:stationt@agglohm.net'>stationt@agglohm.net</a> — 06 59 71 38 57</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>
            </div>
        ";
        $mail->send();
    }

    public function envoyerMailVerification(string $email, string $prenom, string $token): void
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = ConfigurationMail::$host;
        $mail->SMTPAuth   = true;
        $mail->Username   = ConfigurationMail::$username;
        $mail->Password   = ConfigurationMail::$password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = ConfigurationMail::$port;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(ConfigurationMail::$from, ConfigurationMail::$fromName);
        $mail->addAddress($email);

        $lien = ConfigurationSite::$baseUrl . '/verifier-email/' . $token;
        $logoPath = __DIR__ . '/../../ressources/image/Station T.jpg';

        $mail->isHTML(true);
        $mail->Subject = 'Vérifiez votre adresse email — Station T';

        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo_stationt', 'Station T.jpg');
            $logoHtml = '<img src="cid:logo_stationt" alt="Station T" style="max-width:200px; margin-bottom:24px;">';
        } else {
            $logoHtml = '<strong>Station T</strong>';
        }

        $mail->Body = "
            <div style='font-family:Arial,sans-serif; max-width:600px; margin:auto; padding:32px;'>
                {$logoHtml}
                <h2 style='color:#1a1a2e;'>Vérifiez votre adresse email</h2>
                <p>Bonjour {$prenom},</p>
                <p>Merci de vous être inscrit sur <strong>Station T</strong>.</p>
                <p>Cliquez sur le bouton ci-dessous pour valider votre adresse email et accéder à votre espace :</p>
                <p style='margin:32px 0;'>
                    <a href='{$lien}' style='background:#0077cc; color:#fff; padding:12px 24px; border-radius:6px; text-decoration:none; font-weight:bold;'>
                        Vérifier mon adresse email
                    </a>
                </p>
                <p style='color:#666; font-size:13px;'>Ou copiez ce lien dans votre navigateur :<br>{$lien}</p>
                <hr style='border:none; border-top:1px solid #eee; margin:32px 0;'>
                <p style='color:#999; font-size:12px;'>L'équipe Station T — Gigamed</p>

            </div>
        ";

        $mail->send();
    }
}