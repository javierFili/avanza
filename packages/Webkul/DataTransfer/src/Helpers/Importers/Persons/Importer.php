<?php

namespace Webkul\DataTransfer\Helpers\Importers\Persons;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeValueRepository;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use Webkul\DataTransfer\Helpers\Import;
use Webkul\DataTransfer\Helpers\Importers\AbstractImporter;
use Webkul\DataTransfer\Repositories\ImportBatchRepository;

class Importer extends AbstractImporter
{
    /**
     * Error code for non existing email.
     */
    const ERROR_EMAIL_NOT_FOUND_FOR_DELETE = 'email_not_found_to_delete';

    /**
     * Error code for duplicated email.
     */
    const ERROR_DUPLICATE_EMAIL = 'duplicated_email';

    /**
     * Error code for duplicated phone.
     */
    const ERROR_DUPLICATE_PHONE = 'duplicated_phone';

    /**
     * Permanent entity columns.
     */
    protected array $validColumnNames = [
        'contact_numbers',
        'emails',
        'job_title',
        'name',
        'organization_id',
        'user_id',
    ];

    /**
     * Error message templates.
     */
    protected array $messages = [
        self::ERROR_EMAIL_NOT_FOUND_FOR_DELETE  => 'data_transfer::app.importers.persons.validation.errors.email-not-found',
        self::ERROR_DUPLICATE_EMAIL             => 'data_transfer::app.importers.persons.validation.errors.duplicate-email',
        self::ERROR_DUPLICATE_PHONE             => 'data_transfer::app.importers.persons.validation.errors.duplicate-phone',
    ];

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $permanentAttributes = ['emails'];

    /**
     * Permanent entity column.
     */
    protected string $masterAttributeCode = 'unique_id';

    /**
     * Emails storage.
     */
    protected array $emails = [];

    /**
     * Phones storage.
     */
    protected array $phones = [];

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected PersonRepository $personRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        protected Storage $personStorage,
    ) {
        parent::__construct(
            $importBatchRepository,
            $attributeRepository,
            $attributeValueRepository,
        );
    }

    /**
     * Initialize Product error templates.
     */
    protected function initErrorMessages(): void
    {
        foreach ($this->messages as $errorCode => $message) {
            $this->errorHelper->addErrorMessage($errorCode, trans($message));
        }

        parent::initErrorMessages();
    }

    /**
     * Validate data.
     */
    public function validateData(): void
    {
        $this->personStorage->init();

        parent::validateData();
    }

    /**
     * Validates row - VERSIÓN CORREGIDA COMPLETA
     */
    public function validateRow(array $rowData, int $rowNumber): bool
    {
        $rowData = $this->parsedRowData($rowData);

        /**
         * If row is already validated than no need for further validation.
         */
        if (isset($this->validatedRows[$rowNumber])) {
            return ! $this->errorHelper->isRowInvalid($rowNumber);
        }

        $this->validatedRows[$rowNumber] = true;

        /**
         * If import action is delete than no need for further validation.
         */
        if ($this->import->action == Import::ACTION_DELETE) {
            // Verificar que emails sea un array y no esté vacío
            if (is_array($rowData['emails']) && ! empty($rowData['emails'])) {
                foreach ($rowData['emails'] as $email) {
                    if (! $this->isEmailExist($email['value'])) {
                        $this->skipRow($rowNumber, self::ERROR_EMAIL_NOT_FOUND_FOR_DELETE, 'email');

                        return false;
                    }
                }

                return true;
            } else {
                $this->skipRow($rowNumber, self::ERROR_EMAIL_NOT_FOUND_FOR_DELETE, 'email');

                return false;
            }
        }

        // Preparar datos para validación - extraer valores de los arrays
        $validationData = $rowData;

        // Extraer email del formato JSON para validación
        $validationData['emails'] = '';
        if (is_array($rowData['emails']) && ! empty($rowData['emails']) && isset($rowData['emails'][0]['value'])) {
            $validationData['emails'] = $rowData['emails'][0]['value'];
        }

        // Extraer teléfono del formato JSON para validación
        $validationData['contact_numbers'] = '';
        if (is_array($rowData['contact_numbers']) && ! empty($rowData['contact_numbers']) && isset($rowData['contact_numbers'][0]['value'])) {
            $validationData['contact_numbers'] = $rowData['contact_numbers'][0]['value'];
        }

        /**
         * Validate row data.
         */
        $validator = Validator::make($validationData, [
            ...$this->getValidationRules('persons', $validationData),
            'organization_id'         => 'nullable',
            'user_id'                 => 'required|exists:users,id',
            'emails'                  => 'required|email',
            'contact_numbers'         => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $failedAttributes = $validator->failed();

            foreach ($validator->errors()->getMessages() as $attributeCode => $message) {
                $errorCode = array_key_first($failedAttributes[$attributeCode] ?? []);

                $this->skipRow($rowNumber, $errorCode, $attributeCode, current($message));
            }
        }

        return ! $this->errorHelper->isRowInvalid($rowNumber);
    }

    /**
     * Start the import process.
     */
    public function importBatch(ImportBatchContract $batch): bool
    {
        Event::dispatch('data_transfer.imports.batch.import.before', $batch);

        if ($batch->import->action == Import::ACTION_DELETE) {
            $this->deletePersons($batch);
        } else {
            $this->savePersonData($batch);
        }

        /**
         * Update import batch summary.
         */
        $batch = $this->importBatchRepository->update([
            'state' => Import::STATE_PROCESSED,

            'summary'      => [
                'created' => $this->getCreatedItemsCount(),
                'updated' => $this->getUpdatedItemsCount(),
                'deleted' => $this->getDeletedItemsCount(),
            ],
        ], $batch->id);

        Event::dispatch('data_transfer.imports.batch.import.after', $batch);

        return true;
    }

    /**
     * Delete persons from current batch - CORREGIDO
     */
    protected function deletePersons(ImportBatchContract $batch): bool
    {
        /**
         * Load person storage with batch emails.
         */
        $emails = collect($batch->data)->map(function ($row) {
            $parsedRow = $this->parsedRowData($row);

            return collect($parsedRow['emails'])->pluck('value')->toArray();
        })->flatten()->filter()->unique();

        $this->personStorage->load($emails->toArray());

        $idsToDelete = [];

        foreach ($batch->data as $rowData) {
            $rowData = $this->parsedRowData($rowData);

            if (is_array($rowData['emails'])) {
                foreach ($rowData['emails'] as $email) {
                    if ($this->isEmailExist($email['value'])) {
                        $idsToDelete[] = $this->personStorage->get($email['value']);
                    }
                }
            }
        }

        $idsToDelete = array_unique(array_filter($idsToDelete));

        $this->deletedItemsCount = count($idsToDelete);

        if (! empty($idsToDelete)) {
            $this->personRepository->deleteWhere([['id', 'IN', $idsToDelete]]);
        }

        return true;
    }

    /**
     * Save person from current batch - CORREGIDO
     */
    protected function savePersonData(ImportBatchContract $batch): bool
    {
        /**
         * Load person storage with batch email.
         */
        $emails = collect($batch->data)->map(function ($row) {
            $parsedRow = $this->parsedRowData($row);

            return collect($parsedRow['emails'])->pluck('value')->toArray();
        })->flatten()->filter()->unique();

        $this->personStorage->load($emails->toArray());

        $persons = [];

        /**
         * Prepare persons for import.
         */
        foreach ($batch->data as $rowData) {
            $this->preparePersons($rowData, $persons);
        }

        $this->savePersons($persons);

        return true;
    }

    /**
     * Prepare persons from current batch - VERSION CORREGIDA
     */
    public function preparePersons(array $rowData, array &$persons): void
    {
        $rowData = $this->parsedRowData($rowData);

        // Verificar que emails sea un array y no esté vacío
        if (! is_array($rowData['emails']) || empty($rowData['emails'])) {
            return;
        }

        // Procesar cada email
        foreach ($rowData['emails'] as $email) {
            // Crear unique_id para identificar registros únicos
            $contactNumber = null;
            if (is_array($rowData['contact_numbers']) && ! empty($rowData['contact_numbers'])) {
                $contactNumber = $rowData['contact_numbers'][0]['value'] ?? null;
            }

            // Validar que organization_id existe si se proporciona
            $organizationId = null;
            if (! empty($rowData['organization_id'])) {
                $orgId = (int) $rowData['organization_id'];
                // Verificar si la organización existe
                if (\DB::table('organizations')->where('id', $orgId)->exists()) {
                    $organizationId = $orgId;
                } else {
                    \Log::warning("Organization ID {$orgId} does not exist, setting to null");
                }
            }

            // CORREGIDO: Incluir entity_type y otros campos requeridos
            $personData = [
                'name'            => $rowData['name'] ?? '',
                'job_title'       => $rowData['job_title'] ?? null,
                'organization_id' => $organizationId,
                'user_id'         => (int) $rowData['user_id'],
                'emails'          => [$email], // Array - el repositorio lo convertirá a JSON
                'contact_numbers' => $rowData['contact_numbers'] ?? [], // Array - el repositorio lo convertirá a JSON
                'unique_id'       => "{$rowData['user_id']}|{$organizationId}|{$email['value']}|{$contactNumber}",
                'entity_type'     => 'persons', // Requerido por el sistema de atributos
            ];

            // Determinar si es actualización o inserción
            if ($this->isEmailExist($email['value'])) {
                $persons['update'][] = $personData;
            } else {
                $persons['insert'][] = $personData;
            }
        }
    }

    /**
     * Save persons from current batch - VERSION CORREGIDA CON VALIDACIONES
     */
    public function savePersons(array $persons): void
    {
        try {
            // Debug: Log datos antes de guardar
            \Log::info('Saving persons data:', [
                'update_count'  => count($persons['update'] ?? []),
                'insert_count'  => count($persons['insert'] ?? []),
                'sample_insert' => isset($persons['insert'][0]) ? $persons['insert'][0] : null,
            ]);

            // Procesar actualizaciones
            if (! empty($persons['update'])) {
                foreach ($persons['update'] as $personData) {
                    // Buscar la persona existente por email
                    $emailData = $personData['emails'];
                    if (is_array($emailData) && isset($emailData[0]['value'])) {
                        $existingPersonId = $this->personStorage->get($emailData[0]['value']);

                        if ($existingPersonId) {
                            // Actualizar registro existente
                            $this->personRepository->update($personData, $existingPersonId);
                            $this->updatedItemsCount++;
                        }
                    }
                }
            }

            // Procesar inserciones
            if (! empty($persons['insert'])) {
                foreach ($persons['insert'] as $personData) {
                    try {
                        // Verificar que los datos sean válidos antes de crear
                        if (is_array($personData['emails']) && is_array($personData['contact_numbers'])) {

                            // Validación adicional: verificar que user_id existe
                            if (! \DB::table('users')->where('id', $personData['user_id'])->exists()) {
                                \Log::error("User ID {$personData['user_id']} does not exist, skipping person creation");

                                continue;
                            }

                            \Log::info('Creating person with data:', [
                                'name'                  => $personData['name'],
                                'organization_id'       => $personData['organization_id'],
                                'user_id'               => $personData['user_id'],
                                'entity_type'           => $personData['entity_type'],
                                'emails_count'          => count($personData['emails']),
                                'contact_numbers_count' => count($personData['contact_numbers']),
                                'unique_id'             => $personData['unique_id'],
                            ]);

                            // Crear usando el repositorio (que maneja la conversión JSON automáticamente)
                            $createdPerson = $this->personRepository->create($personData);

                            if ($createdPerson) {
                                $this->createdItemsCount++;
                                \Log::info("Person created successfully with ID: {$createdPerson->id}");
                            }

                        } else {
                            \Log::warning('Invalid person data format:', [
                                'emails_type'          => gettype($personData['emails']),
                                'contact_numbers_type' => gettype($personData['contact_numbers']),
                                'data'                 => $personData,
                            ]);
                        }
                    } catch (\Illuminate\Database\QueryException $e) {
                        \Log::error('Database error creating person:', [
                            'error_code'    => $e->getCode(),
                            'error_message' => $e->getMessage(),
                            'data'          => $personData,
                        ]);

                        // Si es error de foreign key, continuar con el siguiente
                        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                            \Log::warning('Skipping person due to foreign key constraint');

                            continue;
                        }

                        throw $e;
                    } catch (\Exception $e) {
                        \Log::error('General error creating person:', [
                            'data'  => $personData,
                            'error' => $e->getMessage(),
                            'file'  => $e->getFile(),
                            'line'  => $e->getLine(),
                            'trace' => $e->getTraceAsString(),
                        ]);

                        // Si el error es sobre entity_type, intentar con más contexto
                        if (strpos($e->getMessage(), 'entity_type') !== false) {
                            \Log::error('Entity type error - trying to debug PersonRepository requirements');

                            // Intentar crear con datos mínimos para debugging
                            try {
                                $minimalData = [
                                    'name'            => $personData['name'],
                                    'user_id'         => $personData['user_id'],
                                    'entity_type'     => 'persons',
                                    'emails'          => $personData['emails'],
                                    'contact_numbers' => $personData['contact_numbers'],
                                ];

                                \Log::info('Attempting minimal person creation:', $minimalData);
                                $this->personRepository->create($minimalData);
                                $this->createdItemsCount++;
                                \Log::info('Minimal person creation succeeded');

                            } catch (\Exception $minimalError) {
                                \Log::error('Even minimal person creation failed:', [
                                    'error' => $minimalError->getMessage(),
                                    'file'  => $minimalError->getFile(),
                                    'line'  => $minimalError->getLine(),
                                ]);
                            }
                        } else {
                            throw $e;
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error saving persons: '.$e->getMessage(), [
                'persons_update_count' => count($persons['update'] ?? []),
                'persons_insert_count' => count($persons['insert'] ?? []),
                'error'                => $e->getTraceAsString(),
                'sample_data'          => isset($persons['insert'][0]) ? $persons['insert'][0] : null,
            ]);

            throw $e;
        }
    }

    /**
     * Check if email exists.
     */
    public function isEmailExist(string $email): bool
    {
        return $this->personStorage->has($email);
    }

    /**
     * Get parsed email and phone - VERSIÓN CORREGIDA COMPLETA
     */
    private function parsedRowData(array $rowData): array
    {
        // Asegurar que emails siempre sea un array
        if (! isset($rowData['emails'])) {
            $rowData['emails'] = [];
        } elseif (is_string($rowData['emails'])) {
            $email = trim($rowData['emails']);
            $rowData['emails'] = ! empty($email) ? [['value' => $email, 'label' => 'work']] : [];
        } elseif (! is_array($rowData['emails'])) {
            $rowData['emails'] = [];
        }

        // Asegurar que contact_numbers siempre sea un array
        if (! isset($rowData['contact_numbers'])) {
            $rowData['contact_numbers'] = [];
        } elseif (is_string($rowData['contact_numbers'])) {
            $phone = trim($rowData['contact_numbers']);
            $rowData['contact_numbers'] = ! empty($phone) ? [['value' => $phone, 'label' => 'work']] : [];
        } elseif (! is_array($rowData['contact_numbers'])) {
            $rowData['contact_numbers'] = [];
        }

        return $rowData;
    }
}
