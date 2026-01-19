<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
import { User, Mail, Lock, Eye, EyeOff } from 'lucide-vue-next';
import { ref } from 'vue';

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
</script>

<template>
    <AuthBase
        title="Creează cont"
        description="Completați datele pentru a vă crea contul"
    >
        <Head title="Înregistrare" />

        <Card class="border-0 shadow-lg">
            <CardHeader class="space-y-1 pb-4">
                <CardTitle class="text-2xl font-bold text-center">
                    Creează cont nou
                </CardTitle>
                <CardDescription class="text-center">
                    Introduceți informațiile pentru a vă crea contul
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="flex flex-col gap-5"
                >
                    <div class="grid gap-5">
                        <div class="grid gap-2">
                            <Label for="name" class="text-sm font-medium">
                                Nume complet
                            </Label>
                            <div class="relative">
                                <User
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="name"
                                    name="name"
                                    placeholder="Nume Prenume"
                                    class="pl-10"
                                />
                            </div>
                            <InputError :message="errors.name" />
                        </div>

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
                                    required
                                    :tabindex="2"
                                    autocomplete="email"
                                    name="email"
                                    placeholder="nume@exemplu.com"
                                    class="pl-10"
                                />
                            </div>
                            <InputError :message="errors.email" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="password" class="text-sm font-medium">
                                Parolă
                            </Label>
                            <div class="relative">
                                <Lock
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    :tabindex="3"
                                    autocomplete="new-password"
                                    name="password"
                                    placeholder="Minim 8 caractere"
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

                        <div class="grid gap-2">
                            <Label
                                for="password_confirmation"
                                class="text-sm font-medium"
                            >
                                Confirmă parola
                            </Label>
                            <div class="relative">
                                <Lock
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                                />
                                <Input
                                    id="password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    required
                                    :tabindex="4"
                                    autocomplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirmați parola"
                                    class="pl-10 pr-10"
                                />
                                <button
                                    type="button"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                                    tabindex="-1"
                                >
                                    <Eye
                                        v-if="!showPasswordConfirmation"
                                        class="h-4 w-4"
                                    />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <InputError
                                :message="errors.password_confirmation"
                            />
                        </div>

                        <Button
                            type="submit"
                            class="mt-2 w-full"
                            size="lg"
                            tabindex="5"
                            :disabled="processing"
                            data-test="register-user-button"
                        >
                            <Spinner v-if="processing" class="mr-2" />
                            {{
                                processing
                                    ? 'Se creează contul...'
                                    : 'Creează cont'
                            }}
                        </Button>
                    </div>

                    <div class="mt-4 text-center text-sm text-muted-foreground">
                        Aveți deja cont?
                        <TextLink
                            :href="login()"
                            class="font-medium text-primary hover:underline"
                            :tabindex="6"
                        >
                            Autentificare
                        </TextLink>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </AuthBase>
</template>
