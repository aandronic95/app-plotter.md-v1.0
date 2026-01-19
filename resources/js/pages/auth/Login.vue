<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Alert, AlertDescription } from '@/components/ui/alert';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { Mail, Lock, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const showPassword = ref(false);
</script>

<template>
    <AuthBase
        title="Autentificare"
        description="Introduceți email-ul și parola pentru a vă conecta"
    >
        <Head title="Autentificare" />

        <Card class="shadow-lg">
            <CardHeader class="space-y-1 pb-4">
                <CardTitle class="text-2xl font-bold text-center">
                    Bun venit înapoi
                </CardTitle>
                <CardDescription class="text-center">
                    Introduceți datele pentru a accesa contul dvs.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Alert
                    v-if="status"
                    class="mb-6 border-green-200 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400"
                >
                    <AlertDescription>
                        {{ status }}
                    </AlertDescription>
                </Alert>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <div class="grid gap-5">
                        <div class="grid gap-2">
                            <Label for="email" class="text-sm font-medium">
                                Adresă email
                            </Label>
                            <div class="relative">
                                <Mail
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="email"
                                    placeholder="nume@exemplu.com"
                                    class="pl-10"
                                />
                            </div>
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-sm font-medium">
                                    Parolă
                                </Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="request()"
                                    class="text-xs text-primary hover:underline"
                                    :tabindex="5"
                                >
                                    Ați uitat parola?
                                </TextLink>
                            </div>
                            <div class="relative">
                                <Lock
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    name="password"
                                    required
                                    :tabindex="2"
                                    autocomplete="current-password"
                                    placeholder="Parola dvs."
                                    class="pl-10 pr-10"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                                    tabindex="-1"
                                >
                                    <Eye v-if="!showPassword" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <InputError :message="errors.password" />
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox
                                id="remember"
                                name="remember"
                                :tabindex="3"
                            />
                            <Label
                                for="remember"
                                class="text-sm font-normal cursor-pointer"
                            >
                                Ține-mă minte
                            </Label>
                        </div>

                        <Button
                            type="submit"
                            class="mt-2 w-full"
                            size="lg"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                        >
                            <Spinner v-if="processing" class="mr-2" />
                            {{ processing ? 'Se conectează...' : 'Conectare' }}
                        </Button>
                    </div>

                    <div
                        class="mt-4 text-center text-sm text-muted-foreground"
                        v-if="canRegister"
                    >
                        Nu aveți cont?
                        <TextLink
                            :href="register()"
                            class="font-medium text-primary hover:underline"
                            :tabindex="5"
                        >
                            Înregistrare
                        </TextLink>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </AuthBase>
</template>
