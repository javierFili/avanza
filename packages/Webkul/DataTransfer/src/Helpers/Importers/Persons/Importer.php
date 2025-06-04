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
     * Prepare persons from current batch - CORREGIDO
     */
    public function preparePersons(array $rowData, array &$persons): void
    {
        $rowData = $this->parsedRowData($rowData);

        // Verificar que emails sea un array y no esté vacío
        if (! is_array($rowData['emails']) || empty($rowData['emails'])) {
            return;
        }

        foreach ($rowData['emails'] as $email) {
            $contactNumber = null;
            if (is_array($rowData['contact_numbers']) && ! empty($rowData['contact_numbers'])) {
                $contactNumber = $rowData['contact_numbers'][0]['value'] ?? null;
            }

            $rowData['unique_id'] = "{$rowData['user_id']}|{$rowData['organization_id']}|{$email['value']}|{$contactNumber}";

            if ($this->isEmailExist($email['value'])) {
                $persons['update'][$email['value']] = $rowData;
            } else {
                $persons['insert'][$email['value']] = [
                    ...$rowData,
                    'created_at' => $rowData['created_at'] ?? now(),
                    'updated_at' => $rowData['updated_at'] ?? now(),
                ];
            }
        }
    }

    /**
     * Save persons from current batch.
     */
    public function savePersons(array $persons): void
    {
        if (! empty($persons['update'])) {
            $this->updatedItemsCount += count($persons['update']);

            $this->personRepository->upsert(
                $persons['update'],
                $this->masterAttributeCode,
            );
        }

        if (! empty($persons['insert'])) {
            $this->createdItemsCount += count($persons['insert']);

            $this->personRepository->insert($persons['insert']);
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
