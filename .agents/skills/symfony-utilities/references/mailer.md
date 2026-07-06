# Mailer (sending email)

Symfony 8.1. Install: `composer require symfony/mailer`. Transport via `MAILER_DSN` in `.env` (e.g. `MAILER_DSN=smtp://user:pass@smtp.example.com:port`).

## 1. Plain email

Type-hint `MailerInterface`, build an `Email`, call `send()`.

```php
// src/Controller/MailerController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class MailerController extends AbstractController
{
    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = new Email()
            ->from('hello@example.com')
            ->to('you@example.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML.</p>');

        $mailer->send($email);

        // ...
        return new Response('Sent');
    }
}
```

The message sends immediately via the configured transport. Done when `$mailer->send($email);` is written.

## 2. Email addresses with a name

```php
use Symfony\Component\Mime\Address;

$email = new Email()
    ->from(new Address('hello@example.com', 'Acme Mailer'))
    ->to(new Address('ryan@example.com', 'Ryan'));
```

## 3. File attachments

Use `addPart()` with `DataPart` + `File`:

```php
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

$email = new Email()
    // ...
    ->addPart(new DataPart(new File('/path/to/terms-of-use.pdf')))
    // custom display name
    ->addPart(new DataPart(new File('/path/to/privacy.pdf'), 'Privacy Policy'))
    // explicit MIME type
    ->addPart(new DataPart(new File('/path/to/contract.doc'), 'Contract', 'application/msword'))
    // from a stream
    ->addPart(new DataPart(fopen('/path/to/contract.doc', 'r')));
```

## 4. Embedded (inline) images

Use `->asInline()` and reference with `cid:` + the part name:

```php
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

$email = new Email()
    // ...
    ->addPart(new DataPart(fopen('/path/to/logo.png', 'r'), 'logo', 'image/png')->asInline())
    ->addPart(new DataPart(new File('/path/to/signature.gif'), 'footer-signature', 'image/gif')->asInline())
    ->html('<img src="cid:logo"> ... <img src="cid:footer-signature"> ...');
```

## 5. Twig HTML email (TemplatedEmail)

Requires Twig: `composer require symfony/twig-bundle`. Use `TemplatedEmail` instead of `Email`.

```php
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

$email = new TemplatedEmail()
    ->from('fabien@example.com')
    ->to(new Address('ryan@example.com'))
    ->subject('Thanks for signing up!')
    // path of the Twig template to render
    ->htmlTemplate('emails/signup.html.twig')
    // optional: locale used in the template
    ->locale('de')
    // variables passed to the template
    ->context([
        'expiration_date' => new \DateTime('+7 days'),
        'username' => 'foo',
    ]);

$mailer->send($email);
```

Template (has access to context vars plus the special `email` variable):

```twig
{# templates/emails/signup.html.twig #}
<h1>Welcome {{ email.toName }}!</h1>
<p>You signed up as {{ username }}.</p>
<p>Valid until {{ expiration_date|date('F jS') }}</p>
```

When using Twig templates, embedded images are handled automatically. If the text part is not set, it is generated from the HTML. Set it explicitly with `->text('...')` or `->textTemplate('emails/signup.txt.twig')`.

## 6. Async sending

If Messenger is installed, emails are sent asynchronously by default (the `send()` call queues the message). No code change needed; just ensure a Messenger transport is configured.

STOP once `$mailer->send($email);` is written with the correct `Email` / `TemplatedEmail` setup.
