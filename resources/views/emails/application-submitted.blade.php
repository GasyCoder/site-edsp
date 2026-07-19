<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmation de votre inscription EDSP</title>
</head>
<body style="margin:0;background:#f4f6fb;color:#17233f;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:32px 16px;background:#f4f6fb;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:620px;overflow:hidden;border-radius:16px;background:#ffffff;box-shadow:0 12px 36px rgba(11,31,85,.10);">
                    <tr><td style="height:6px;background:#078b3e;"></td></tr>
                    <tr>
                        <td style="padding:36px 40px;">
                            <p style="margin:0 0 10px;color:#078b3e;font-size:12px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;">Inscription EDSP</p>
                            <h1 style="margin:0;color:#0b1f55;font-size:27px;line-height:1.3;">Votre dossier a bien été reçu</h1>
                            <p style="margin:20px 0 0;color:#52617d;font-size:16px;line-height:1.7;">
                                Bonjour {{ $application->first_name }} {{ $application->last_name }}, votre demande d’inscription a été enregistrée et sera étudiée par l’équipe de l’EDSP.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:26px 0;border:1px solid #dce3ef;border-radius:12px;background:#f8fafc;">
                                <tr>
                                    <td style="padding:18px 20px;border-bottom:1px solid #dce3ef;color:#71809c;font-size:13px;">Numéro de dossier</td>
                                    <td align="right" style="padding:18px 20px;border-bottom:1px solid #dce3ef;color:#0b1f55;font-size:16px;font-weight:700;">{{ $application->application_number }}</td>
                                </tr>
                                @if ($application->academicLevel || $application->mention || $application->parcours)
                                    <tr>
                                        <td style="padding:18px 20px;color:#71809c;font-size:13px;">Orientation demandée</td>
                                        <td align="right" style="padding:18px 20px;color:#0b1f55;font-size:14px;font-weight:700;">
                                            {{ $application->academicLevel?->code }}
                                            @if ($application->mention) · {{ $application->mention->nom }} @endif
                                            @if ($application->parcours) · {{ $application->parcours->nom }} @endif
                                        </td>
                                    </tr>
                                @endif
                            </table>

                            <p style="margin:0;color:#52617d;font-size:14px;line-height:1.7;">Conservez précieusement ce numéro : il permettra d’identifier votre dossier lors de vos échanges avec l’administration.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 40px;background:#0b1f55;color:#c9d4ee;font-size:12px;line-height:1.6;">
                            École de Droit et Science Politique · Ambondrona, Mahajanga
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
