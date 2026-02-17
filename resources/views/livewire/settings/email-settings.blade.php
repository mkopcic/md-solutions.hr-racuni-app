<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Email Settings')" :subheading="__('Configure your email and SMTP settings')" :wide="true">

        <div class="space-y-8">
            <!-- Main Form -->
            <form wire:submit="updateEmailSettings" class="space-y-6">

                    <!-- Basic Email Settings Card -->
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                        <flux:heading size="sm" class="mb-5">{{ __('Mail Configuration') }}</flux:heading>

                        <div class="grid gap-5 md:grid-cols-2">
                            <!-- Mail Mailer -->
                            <flux:field>
                                <flux:label>{{ __('Mail Driver') }}</flux:label>
                                <flux:select wire:model="mail_mailer">
                                    <option value="smtp">SMTP</option>
                                    <option value="sendmail">Sendmail</option>
                                    <option value="mailgun">Mailgun</option>
                                    <option value="ses">Amazon SES</option>
                                    <option value="postmark">Postmark</option>
                                    <option value="log">Log (Development)</option>
                                </flux:select>
                                <flux:error name="mail_mailer" />
                            </flux:field>

                            <!-- From Address -->
                            <flux:field>
                                <flux:label>{{ __('From Email') }}</flux:label>
                                <flux:input wire:model="mail_from_address" type="email" placeholder="noreply@example.com" />
                                <flux:error name="mail_from_address" />
                            </flux:field>
                        </div>

                        <!-- From Name (Full Width) -->
                        <flux:field class="mt-4">
                            <flux:label>{{ __('From Name') }}</flux:label>
                            <flux:input wire:model="mail_from_name" type="text" placeholder="{{ config('app.name') }}" />
                            <flux:error name="mail_from_name" />
                        </flux:field>
                    </div>

                    <!-- SMTP Settings Card (Conditional) -->
                    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900" x-show="$wire.mail_mailer === 'smtp'">
                        <flux:heading size="sm" class="mb-5">{{ __('SMTP Server Settings') }}</flux:heading>

                        <div class="grid gap-5 md:grid-cols-2">
                            <!-- SMTP Host -->
                            <flux:field>
                                <flux:label>{{ __('Host') }}</flux:label>
                                <flux:input wire:model="mail_host" type="text" placeholder="smtp.mailtrap.io" />
                                <flux:error name="mail_host" />
                            </flux:field>

                            <!-- SMTP Port -->
                            <flux:field>
                                <flux:label>{{ __('Port') }}</flux:label>
                                <flux:input wire:model="mail_port" type="number" placeholder="587" />
                                <flux:error name="mail_port" />
                            </flux:field>

                            <!-- SMTP Username -->
                            <flux:field>
                                <flux:label>{{ __('Username') }}</flux:label>
                                <flux:input wire:model="mail_username" type="text" placeholder="username" />
                                <flux:error name="mail_username" />
                            </flux:field>

                            <!-- SMTP Password -->
                            <flux:field>
                                <flux:label>{{ __('Password') }}</flux:label>
                                <flux:input wire:model="mail_password" type="password" placeholder="••••••••" />
                                <flux:error name="mail_password" />
                            </flux:field>

                            <!-- SMTP Encryption -->
                            <flux:field>
                                <flux:label>{{ __('Encryption') }}</flux:label>
                                <flux:select wire:model="mail_encryption">
                                    <option value="tls">TLS (Port 587)</option>
                                    <option value="ssl">SSL (Port 465)</option>
                                    <option value="none">None</option>
                                </flux:select>
                                <flux:error name="mail_encryption" />
                            </flux:field>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3">
                        <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ __('Save Settings') }}</span>
                            <span wire:loading>{{ __('Saving...') }}</span>
                        </flux:button>

                        <x-action-message class="me-3" on="email-settings-updated">
                            {{ __('Saved.') }}
                        </x-action-message>
                    </div>
                </form>

            <!-- Current Config + Test Email in 2 Columns -->
            <div class="grid gap-6 md:grid-cols-2">

                <!-- Current Config Display -->
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-5">{{ __('Active Configuration') }}</flux:heading>
                    <div class="space-y-3 text-sm">
                        <div class="flex flex-col gap-1">
                            <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Mail Driver') }}</flux:text>
                            <flux:text class="font-mono">{{ config('mail.default') ?: 'Not configured' }}</flux:text>
                        </div>
                        <flux:separator />
                        <div class="flex flex-col gap-1">
                            <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('SMTP Host') }}</flux:text>
                            <flux:text class="font-mono break-all">{{ config('mail.mailers.smtp.host') ?: 'Not configured' }}</flux:text>
                        </div>
                        <div class="flex flex-col gap-1">
                            <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('SMTP Port') }}</flux:text>
                            <flux:text class="font-mono">{{ config('mail.mailers.smtp.port') ?: 'Not configured' }}</flux:text>
                        </div>
                        <flux:separator />
                        <div class="flex flex-col gap-1">
                            <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('From Address') }}</flux:text>
                            <flux:text class="break-all">{{ config('mail.from.address') ?: 'Not configured' }}</flux:text>
                        </div>
                        <div class="flex flex-col gap-1">
                            <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('From Name') }}</flux:text>
                            <flux:text>{{ config('mail.from.name') ?: 'Not configured' }}</flux:text>
                        </div>
                    </div>
                </div>

                <!-- Test Email Section -->
                <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="sm" class="mb-3">{{ __('Test Email') }}</flux:heading>
                    <flux:subheading class="mb-5">{{ __('Send a test email to verify your SMTP settings') }}</flux:subheading>

                    <div class="space-y-4">
                        <flux:field>
                            <flux:label>{{ __('Recipient Email') }}</flux:label>
                            <flux:input wire:model="test_email" type="email" placeholder="test@example.com" />
                            <flux:error name="test_email" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Email Subject') }}</flux:label>
                            <flux:input wire:model="test_subject" type="text" placeholder="Test Email" />
                            <flux:error name="test_subject" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Email Message') }}</flux:label>
                            <flux:textarea wire:model="test_message" rows="4" placeholder="Enter your test message..." />
                            <flux:error name="test_message" />
                        </flux:field>

                        <flux:button wire:click="sendTestEmail" variant="filled" class="w-full" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="sendTestEmail">{{ __('Send Test Email') }}</span>
                            <span wire:loading wire:target="sendTestEmail">{{ __('Sending...') }}</span>
                        </flux:button>

                        @if ($testEmailSuccess)
                            <flux:callout icon="check-circle" variant="subtle" color="green">
                                {{ $testEmailSuccess }}
                            </flux:callout>
                        @endif

                        @if ($testEmailError)
                            <flux:callout icon="exclamation-triangle" variant="subtle" color="red">
                                {{ $testEmailError }}
                            </flux:callout>
                        @endif
                    </div>
                </div>

            </div>

        </div>

        <flux:callout icon="information-circle" variant="subtle" color="zinc" class="mt-6">
            {{ __('Changes are saved to .env file. Config cache is cleared automatically.') }}
        </flux:callout>

    </x-settings.layout>
</section>
