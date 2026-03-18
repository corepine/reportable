# corepine/reportable

Reusable polymorphic reporting for Laravel models with Livewire.

`corepine/reportable` gives you a small reporting foundation that you can reuse across reviews, comments, posts, and any other Eloquent model. It ships with:

- polymorphic `reporter` and `reportable` relations
- `HasReports` and `CanReport` model concerns
- config and enum backed report types and statuses
- a report manager/service for creating and resolving reports
- **Livewire components** for report modal and report inbox with encrypted parameters
- starter Blade components and routes

## Installation

```bash
composer require corepine/reportable
php artisan reportable:install --migrate
```

## Quick Start

Add `HasReports` to models that may be reported:

```php
use Corepine\Reportable\Models\Concerns\HasReports;

class Post extends Model
{
    use HasReports;
}
```

Add `CanReport` to the actor model:

```php
use Corepine\Reportable\Models\Concerns\CanReport;

class User extends Authenticatable
{
    use CanReport;
}
```

## Using Livewire Components

### Report Modal with Encrypted Parameters

The package includes a Livewire-powered report modal that automatically encrypts the reportable type and ID for security:

```blade
{{-- Place the modal component once in your layout --}}
<x-corepine-report-modal />

{{-- Use the button component to trigger the modal --}}
<x-corepine-report-button :reportable="$post" label="Report Post" />
```

The button component automatically:
- Encrypts the model type and ID using Laravel's Crypt facade
- Dispatches a Livewire event to open the modal
- Passes encrypted parameters securely

### Manual Usage with Encrypted Parameters

You can also trigger the modal manually using JavaScript:

```blade
@php
    use Illuminate\Support\Facades\Crypt;
    $encryptedType = Crypt::encryptString($post->getMorphClass());
    $encryptedId = Crypt::encryptString((string) $post->getKey());
@endphp

<button 
    wire:click="$dispatch('openReportModal', { 
        encryptedType: '{{ $encryptedType }}', 
        encryptedId: '{{ $encryptedId }}' 
    })"
>
    Report
</button>

{{-- Include modal component --}}
<x-corepine-report-modal />
```

### Using the Encryption Helper

```php
use Corepine\Reportable\Support\ReportableEncryption;

// Encrypt reportable model
$encrypted = ReportableEncryption::encryptReportable($post);
// Returns: ['encryptedType' => '...', 'encryptedId' => '...']

// Decrypt parameters
$decrypted = ReportableEncryption::decryptReportable($encryptedType, $encryptedId);
// Returns: ['type' => 'App\Models\Post', 'id' => '123']
```

### Reports Index with Livewire

The reports index page uses Livewire for real-time filtering and pagination:

```blade
{{-- In your layout --}}
@livewire('corepine-reports-index')
```

Or use the included page route:
```
/corepine/reportable/reports
```

### Modal Auto-Close

The Livewire modal automatically closes after a successful report submission and flashes a success message to the session.

## Create a report in code:

```php
$user->report($post, 'obscene', [
    'message' => 'This content is inappropriate'
]);