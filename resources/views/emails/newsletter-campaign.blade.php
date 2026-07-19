<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $campaign->subject }}</title>
    <style>
        .newsletter-content p { margin: 0 0 18px; }
        .newsletter-content h2 { margin: 26px 0 12px; color: #0b1f55; font-size: 21px; line-height: 1.35; }
        .newsletter-content h3 { margin: 22px 0 10px; color: #0b1f55; font-size: 18px; line-height: 1.4; }
        .newsletter-content ul, .newsletter-content ol { margin: 0 0 18px; padding-left: 22px; }
        .newsletter-content li { margin: 7px 0; }
        .newsletter-content a { color: #087a3a; font-weight: 700; }
        @media only screen and (max-width: 620px) {
            .email-shell { width: 100% !important; }
            .email-padding { padding-left: 22px !important; padding-right: 22px !important; }
            .event-cell { display: block !important; width: 100% !important; padding: 0 0 14px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,'Helvetica Neue',sans-serif;color:#253858;">
    <span style="display:none!important;max-height:0;overflow:hidden;opacity:0;color:transparent;">{{ $campaign->preheader ?: $campaign->title }}</span>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f4f6f8;">
        <tr>
            <td align="center" style="padding:32px 12px;">
                <table role="presentation" class="email-shell" width="640" cellspacing="0" cellpadding="0" border="0" style="width:640px;max-width:640px;background:#ffffff;border:1px solid #dfe5ec;">
                    <tr><td height="4" style="height:4px;background:#078b3e;font-size:0;line-height:0;">&nbsp;</td></tr>

                    <tr>
                        <td class="email-padding" style="padding:23px 34px 20px;border-bottom:1px solid #e6ebf0;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td width="58" valign="middle" style="width:58px;">
                                        <img src="{{ $message->embed(public_path('images/logo-edsp.png')) }}" width="46" height="46" alt="Logo EDSP" style="display:block;width:46px;height:46px;object-fit:contain;">
                                    </td>
                                    <td valign="middle">
                                        <p style="margin:0;color:#0b1f55;font-size:16px;font-weight:800;line-height:1.2;">EDSP</p>
                                        <p style="margin:4px 0 0;color:#526681;font-size:12px;line-height:1.4;">École de Droit et Sciences Politique</p>
                                    </td>
                                    <td align="right" valign="middle">
                                        <span style="display:inline-block;padding:6px 9px;background:#edf8f1;color:#087a3a;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">
                                            {{ match ($campaign->type) { 'event' => 'Événement', 'event_cancellation' => 'Annulation', 'news' => 'Actualité', default => 'Information' } }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td class="email-padding" style="padding:31px 34px 25px;">
                            @if ($subscriber->name)
                                <p style="margin:0 0 12px;color:#526681;font-size:14px;line-height:1.5;">Bonjour {{ $subscriber->name }},</p>
                            @endif
                            <h1 style="margin:0;color:#0b1f55;font-size:30px;font-weight:750;line-height:1.22;letter-spacing:-.02em;">{{ $campaign->title }}</h1>
                            @if ($campaign->preheader)
                                <p style="margin:13px 0 0;color:#526681;font-size:16px;line-height:1.6;">{{ $campaign->preheader }}</p>
                            @endif
                        </td>
                    </tr>

                    @if (in_array($campaign->type, ['event', 'event_cancellation'], true) && ($campaign->event_starts_at || $campaign->event_location))
                        <tr>
                            <td class="email-padding" style="padding:0 34px 27px;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f7f9fb;border-top:2px solid #078b3e;">
                                    <tr>
                                        @if ($campaign->event_starts_at)
                                            <td class="event-cell" width="50%" valign="top" style="width:50%;padding:17px 19px;">
                                                <p style="margin:0 0 5px;color:#708198;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;">Date et heure</p>
                                                <p style="margin:0;color:#0b1f55;font-size:14px;font-weight:700;line-height:1.5;">{{ $campaign->event_starts_at->translatedFormat('d F Y à H:i') }}</p>
                                            </td>
                                        @endif
                                        @if ($campaign->event_location)
                                            <td class="event-cell" width="50%" valign="top" style="width:50%;padding:17px 19px;border-left:1px solid #e1e7ed;">
                                                <p style="margin:0 0 5px;color:#708198;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;">Lieu</p>
                                                <p style="margin:0;color:#0b1f55;font-size:14px;font-weight:700;line-height:1.5;">{{ $campaign->event_location }}</p>
                                            </td>
                                        @endif
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td class="email-padding newsletter-content" style="padding:0 34px 12px;color:#344763;font-size:16px;line-height:1.75;">
                            {!! $campaign->content !!}
                        </td>
                    </tr>

                    @if ($attachmentExists && $isInlineImage && $attachmentData)
                        <tr>
                            <td class="email-padding" style="padding:13px 34px 25px;">
                                <p style="margin:0 0 10px;color:#708198;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;">Illustration jointe</p>
                                <img
                                    src="{{ $message->embedData($attachmentData, $campaign->attachment_name ?: 'illustration', $attachmentMimeType) }}"
                                    width="572"
                                    alt="{{ $campaign->attachment_name ?: 'Illustration du message' }}"
                                    style="display:block;width:100%;max-width:572px;height:auto;border:1px solid #e1e7ed;"
                                >
                                <p style="margin:9px 0 0;color:#708198;font-size:12px;line-height:1.5;">
                                    {{ $campaign->attachment_name }}
                                    @if ($attachmentSize)
                                        · {{ $attachmentSize }}
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @elseif ($attachmentExists)
                        <tr>
                            <td class="email-padding" style="padding:13px 34px 25px;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #dfe5ec;background:#fafbfc;">
                                    <tr>
                                        <td width="58" valign="middle" style="width:58px;padding:15px 0 15px 16px;">
                                            <span style="display:inline-block;min-width:38px;padding:10px 4px;background:#0b1f55;color:#fff;font-size:10px;font-weight:800;text-align:center;text-transform:uppercase;">
                                                {{ strtoupper(pathinfo($campaign->attachment_name ?: '', PATHINFO_EXTENSION) ?: 'DOC') }}
                                            </span>
                                        </td>
                                        <td valign="middle" style="padding:15px 12px;">
                                            <p style="margin:0;color:#0b1f55;font-size:14px;font-weight:700;line-height:1.4;">{{ $campaign->attachment_name }}</p>
                                            <p style="margin:4px 0 0;color:#708198;font-size:12px;line-height:1.4;">
                                                Pièce jointe à cet e-mail
                                                @if ($attachmentSize)
                                                    · {{ $attachmentSize }}
                                                @endif
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    @if ($campaign->external_url)
                        <tr>
                            <td class="email-padding" style="padding:4px 34px 32px;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top:1px solid #e1e7ed;border-bottom:1px solid #e1e7ed;">
                                    <tr>
                                        <td style="padding:16px 0;">
                                            <p style="margin:0 0 4px;color:#708198;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;">Lien utile</p>
                                            <p style="margin:0;color:#0b1f55;font-size:14px;font-weight:700;line-height:1.4;">{{ $campaign->external_url_label ?: 'Consulter la ressource' }}</p>
                                            @if ($externalHost)
                                                <p style="margin:4px 0 0;color:#708198;font-size:12px;">{{ $externalHost }}</p>
                                            @endif
                                        </td>
                                        <td align="right" style="padding:16px 0 16px 15px;">
                                            <a href="{{ $campaign->external_url }}" style="display:inline-block;background:#078b3e;color:#ffffff;text-decoration:none;font-size:13px;font-weight:800;padding:11px 16px;">Ouvrir le lien&nbsp;→</a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td class="email-padding" style="padding:22px 34px;background:#f8fafb;border-top:1px solid #e1e7ed;">
                            <p style="margin:0;color:#526681;font-size:12px;line-height:1.65;">
                                École de Droit et Sciences Politique · Mahajanga<br>
                                Vous recevez ce message car votre adresse est inscrite à la newsletter de l’EDSP.
                            </p>
                            <p style="margin:10px 0 0;font-size:12px;line-height:1.5;">
                                <a href="{{ $unsubscribeUrl }}" style="color:#526681;text-decoration:underline;">Se désinscrire</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
