<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'question' => 'Quelles formations sont proposées par l’EDSP ?',
                'answer' => '<p>L’EDSP organise son offre autour de deux mentions : <strong>Droit</strong> et <strong>Sciences Politiques</strong>. Les parcours proposés évoluent selon le niveau d’études. Consultez la <a href="/formations">page Formations</a> pour voir l’organisation détaillée.</p>',
                'category' => 'Formations',
                'en' => [
                    'question' => 'Which programmes does EDSP offer?',
                    'answer' => '<p>EDSP organises its academic offer around two subject areas: <strong>Law</strong> and <strong>Political Science</strong>. Available pathways vary by level. Visit the <a href="/formations">Programmes page</a> for the detailed structure.</p>',
                    'category' => 'Programmes',
                ],
            ],
            [
                'question' => 'Quels diplômes peut-on préparer à l’EDSP ?',
                'answer' => '<p>L’EDSP s’inscrit dans le système LMD et prépare aux diplômes de <strong>Licence (L3)</strong> et de <strong>Master (M2)</strong>, avec une progression des enseignements de la L1 à la M2.</p>',
                'category' => 'Formations',
                'en' => [
                    'question' => 'Which degrees can students prepare for at EDSP?',
                    'answer' => '<p>EDSP follows the LMD system and prepares students for the <strong>Bachelor’s degree (L3)</strong> and the <strong>Master’s degree (M2)</strong>, with courses progressing from L1 to M2.</p>',
                    'category' => 'Programmes',
                ],
            ],
            [
                'question' => 'Comment connaître le parcours correspondant à mon niveau ?',
                'answer' => '<p>Les parcours sont associés à des niveaux précis et certaines années peuvent constituer un tronc commun. La <a href="/formations">page Formations</a> présente les affectations officielles par mention, parcours et niveau.</p>',
                'category' => 'Formations',
                'en' => [
                    'question' => 'How can I find the pathway for my level?',
                    'answer' => '<p>Pathways are linked to specific levels, and some years may form a common core. The <a href="/formations">Programmes page</a> shows the official organisation by subject area, pathway and level.</p>',
                    'category' => 'Programmes',
                ],
            ],
            [
                'question' => 'Quand peut-on déposer une inscription ?',
                'answer' => '<p>Une inscription peut être déposée lorsqu’une campagne est ouverte. Les dates d’ouverture et de clôture sont affichées sur la <a href="/inscription">page d’inscription</a> et dans les communications officielles de l’établissement.</p>',
                'category' => 'Inscriptions',
                'en' => [
                    'question' => 'When can I submit an application?',
                    'answer' => '<p>An application can be submitted while an admission round is open. Opening and closing dates appear on the <a href="/inscription">application page</a> and in the School’s official announcements.</p>',
                    'category' => 'Applications',
                ],
            ],
            [
                'question' => 'Comment déposer mon dossier en ligne ?',
                'answer' => '<p>Lorsque la campagne est ouverte, rendez-vous sur la <a href="/inscription">page d’inscription</a>. Complétez successivement les informations d’identité, familiales et pédagogiques, ajoutez les pièces demandées, puis vérifiez le récapitulatif avant l’envoi final.</p>',
                'category' => 'Inscriptions',
                'en' => [
                    'question' => 'How do I submit my application online?',
                    'answer' => '<p>While an admission round is open, go to the <a href="/inscription">application page</a>. Complete the identity, family and academic sections, attach the requested documents, then review the summary before final submission.</p>',
                    'category' => 'Applications',
                ],
            ],
            [
                'question' => 'Quelles pièces justificatives faut-il fournir ?',
                'answer' => '<p>La liste exacte dépend de la campagne et de la formation choisie. Les pièces obligatoires sont indiquées directement dans le formulaire avant l’envoi. Référez-vous toujours à la liste officielle affichée pour la campagne en cours.</p>',
                'category' => 'Inscriptions',
                'en' => [
                    'question' => 'Which supporting documents are required?',
                    'answer' => '<p>The exact list depends on the admission round and selected programme. Required documents are shown directly in the form before submission. Always refer to the official list for the current round.</p>',
                    'category' => 'Applications',
                ],
            ],
            [
                'question' => 'Un tutoriel est-il disponible pour remplir le formulaire ?',
                'answer' => '<p>Lorsqu’un tutoriel vidéo est associé à la campagne, son lien apparaît dans l’encadré d’information de la page d’inscription. Il présente les principales étapes à suivre avant l’envoi du dossier.</p>',
                'category' => 'Inscriptions',
                'en' => [
                    'question' => 'Is there a tutorial for completing the form?',
                    'answer' => '<p>When a video tutorial is available for an admission round, its link appears in the information panel on the application page. It explains the main steps to complete before submission.</p>',
                    'category' => 'Applications',
                ],
            ],
            [
                'question' => 'Que se passe-t-il après l’envoi de mon dossier ?',
                'answer' => '<p>Après un envoi réussi, un numéro de dossier est généré et une notification est transmise à l’adresse e-mail indiquée. Conservez ce numéro : il permet à l’administration d’identifier rapidement votre demande.</p>',
                'category' => 'Inscriptions',
                'en' => [
                    'question' => 'What happens after I submit my application?',
                    'answer' => '<p>After successful submission, an application number is generated and a notification is sent to the email address provided. Keep this number, as it helps the administration identify your application quickly.</p>',
                    'category' => 'Applications',
                ],
            ],
            [
                'question' => 'Puis-je corriger un dossier déjà envoyé ?',
                'answer' => '<p>Le formulaire ne doit pas être soumis une seconde fois pour une simple correction. Contactez l’administration en précisant votre numéro de dossier, votre nom et l’information à rectifier afin de recevoir la marche à suivre.</p>',
                'category' => 'Inscriptions',
                'en' => [
                    'question' => 'Can I correct an application after submission?',
                    'answer' => '<p>Do not submit the form again for a simple correction. Contact the administration with your application number, name and the information to be corrected so that you can receive further instructions.</p>',
                    'category' => 'Applications',
                ],
            ],
            [
                'question' => 'Où consulter les conditions et informations officielles ?',
                'answer' => '<p>Consultez les pages <a href="/admissions">Admissions</a>, <a href="/actualites">Actualités</a> et <a href="/documents">Documents publics</a>. Les avis et documents publiés par l’établissement prévalent sur toute information non officielle.</p>',
                'category' => 'Informations pratiques',
                'en' => [
                    'question' => 'Where can I find official requirements and information?',
                    'answer' => '<p>Visit the <a href="/admissions">Admissions</a>, <a href="/actualites">News</a> and <a href="/documents">Public documents</a> pages. Notices and documents published by the School take precedence over unofficial information.</p>',
                    'category' => 'Practical information',
                ],
            ],
            [
                'question' => 'Comment accéder aux ressources de la bibliothèque ?',
                'answer' => '<p>La rubrique <a href="/bibliotheque">Bibliothèque</a> présente les ressources documentaires et les modalités d’accès mises à disposition par l’établissement.</p>',
                'category' => 'Services',
                'en' => [
                    'question' => 'How can I access library resources?',
                    'answer' => '<p>The <a href="/bibliotheque">Library section</a> presents the documentary resources and access arrangements provided by the School.</p>',
                    'category' => 'Services',
                ],
            ],
            [
                'question' => 'Comment recevoir les actualités de l’EDSP par e-mail ?',
                'answer' => '<p>Inscrivez votre adresse dans le formulaire de newsletter situé en bas du site, puis confirmez l’inscription depuis l’e-mail reçu. Cette confirmation protège votre adresse contre les inscriptions non souhaitées.</p>',
                'category' => 'Services',
                'en' => [
                    'question' => 'How can I receive EDSP news by email?',
                    'answer' => '<p>Enter your address in the newsletter form at the bottom of the website, then confirm your subscription from the email you receive. This confirmation protects your address from unwanted subscriptions.</p>',
                    'category' => 'Services',
                ],
            ],
            [
                'question' => 'Comment contacter l’administration de l’EDSP ?',
                'answer' => '<p>Utilisez la <a href="/contact">page Contact</a> pour envoyer votre demande. Vous pouvez également utiliser le téléphone, l’adresse e-mail ou l’adresse du campus affichés dans l’en-tête et le pied de page du site.</p>',
                'category' => 'Services',
                'en' => [
                    'question' => 'How can I contact the EDSP administration?',
                    'answer' => '<p>Use the <a href="/contact">Contact page</a> to send your request. You can also use the telephone number, email address or campus address shown in the website header and footer.</p>',
                    'category' => 'Services',
                ],
            ],
        ];

        foreach ($items as $position => $item) {
            Faq::query()->updateOrCreate(
                ['question' => $item['question']],
                [
                    'answer' => $item['answer'],
                    'category' => $item['category'],
                    'position' => ($position + 1) * 10,
                    'is_visible' => true,
                    'translations' => ['en' => $item['en']],
                ],
            );
        }
    }
}
