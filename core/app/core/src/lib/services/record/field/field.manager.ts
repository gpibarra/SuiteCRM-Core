/**
 * SuiteCRM is a customer relationship management program developed by SalesAgility Ltd.
 * Copyright (C) 2021 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SALESAGILITY, SALESAGILITY DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Supercharged by SuiteCRM" logo. If the display of the logos is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Supercharged by SuiteCRM".
 */

import {Field, FieldDefinition, Record, ViewFieldDefinition} from 'common';
import {LanguageStore} from '../../../store/language/language.store';
import {AsyncValidatorFn, FormControl, ValidatorFn} from '@angular/forms';
import {Injectable} from '@angular/core';
import {ValidationManager} from '../validation/validation.manager';
import {DataTypeFormatter} from '../../formatters/data-type.formatter.service';
import {SavedFilter} from '../../../store/saved-filters/saved-filter.model';

@Injectable({
    providedIn: 'root'
})
export class FieldManager {

    constructor(protected validationManager: ValidationManager, protected typeFormatter: DataTypeFormatter) {
    }

    /**
     * Build minimally initialised field object
     *
     * @param {string} type field type
     * @param {string} value field value
     * @returns {object} Field
     */
    public buildShallowField(type: string, value: string): Field {
        return {
            type,
            value,
            definition: {
                type
            }
        } as Field;
    }

    /**
     * Build and add field to record
     *
     * @param {object} record Record
     * @param {object} viewField ViewFieldDefinition
     * @param {object} language LanguageStore
     * @returns {object}Field
     */
    public addField(record: Record, viewField: ViewFieldDefinition, language: LanguageStore = null): Field {

        const field = this.buildField(record, viewField, language);

        this.addToRecord(record, viewField.name, field);
        this.addGroupFields(
            record,
            viewField,
            language,
            this.isFieldInitialized.bind(this),
            this.buildField.bind(this),
            this.addToRecord.bind(this)
        );

        return field;
    }

    /**
     * Build field
     *
     * @param {object} record Record
     * @param {object} viewField ViewFieldDefinition
     * @param {object} language LanguageStore
     * @returns {object}Field
     */
    public buildField(record: Record, viewField: ViewFieldDefinition, language: LanguageStore = null): Field {

        const definition = (viewField && viewField.fieldDefinition) || {} as FieldDefinition;
        const {value, valueList, valueObject} = this.parseValue(viewField, definition, record);
        const {validators, asyncValidators} = this.getSaveValidators(record, viewField);

        return this.setupField(
            record.module,
            viewField,
            value,
            valueList,
            valueObject,
            record,
            definition,
            validators,
            asyncValidators,
            language
        );
    }

    /**
     * Build and add filter field to record
     *
     * @param {object} record Record
     * @param {object} viewField ViewFieldDefinition
     * @param {object} language LanguageStore
     * @returns {object}Field
     */
    public addFilterField(record: SavedFilter, viewField: ViewFieldDefinition, language: LanguageStore = null): Field {

        const field = this.buildFilterField(record, viewField, language);

        this.addToSavedFilter(record, viewField.name, field);
        this.addGroupFields(
            record,
            viewField,
            language,
            this.isCriteriaFieldInitialized.bind(this),
            this.buildFilterField.bind(this),
            this.addToSavedFilter.bind(this)
        );

        return field;
    }

    /**
     * Build filter field
     *
     * @param {object} savedFilter SavedFilter
     * @param {object} viewField ViewFieldDefinition
     * @param {object} language LanguageStore
     * @returns {object}Field
     */
    public buildFilterField(savedFilter: SavedFilter, viewField: ViewFieldDefinition, language: LanguageStore = null): Field {

        const definition = (viewField && viewField.fieldDefinition) || {} as FieldDefinition;
        const {validators, asyncValidators} = this.getFilterValidators(savedFilter, viewField);

        return this.setupField(
            savedFilter.searchModule,
            viewField,
            null,
            null,
            null,
            savedFilter,
            definition,
            validators,
            asyncValidators,
            language
        );
    }

    public getFieldLabel(label: string, module: string, language: LanguageStore): string {
        const languages = language.getLanguageStrings();
        return language.getFieldLabel(label, module, languages);
    }

    /**
     * Add field to record
     *
     * @param {object} record Record
     * @param {string} name string
     * @param {object} field Field
     */
    public addToRecord(record: Record, name: string, field: Field): void {

        if (!record || !name || !field) {
            return;
        }

        if (!record.fields) {
            record.fields = {};
        }

        record.fields[name] = field;

        if (record.formGroup && field.formControl) {
            record.formGroup.addControl(name, field.formControl);
        }
    }

    /**
     * Add field to SavedFilter
     *
     * @param {object} savedFilter SavedFilter
     * @param {string} name string
     * @param {object} field Field
     */
    public addToSavedFilter(savedFilter: SavedFilter, name: string, field: Field): void {

        if (!savedFilter || !name || !field) {
            return;
        }

        if (!savedFilter.criteriaFields) {
            savedFilter.criteriaFields = {};
        }

        savedFilter.criteriaFields[name] = field;

        if (savedFilter.criteriaFormGroup && field.formControl) {
            savedFilter.criteriaFormGroup.addControl(name, field.formControl);
        }
    }

    /**
     * Create and add group fields to record
     *
     * @param {object} record Record
     * @param {object} viewField ViewFieldDefinition
     * @param {object} language LanguageStore
     * @param {function} isInitializedFunction
     * @param {function} buildFieldFunction
     * @param {function} addRecordFunction
     */
    protected addGroupFields(
        record: Record,
        viewField: ViewFieldDefinition,
        language: LanguageStore,
        isInitializedFunction: Function,
        buildFieldFunction: Function,
        addRecordFunction: Function,
    ): void {

        const definition = (viewField && viewField.fieldDefinition) || {};
        const groupFields = definition.groupFields || {};
        const groupFieldsKeys = Object.keys(groupFields);

        groupFieldsKeys.forEach(key => {
            const fieldDefinition = groupFields[key];

            if (isInitializedFunction(record, key)) {
                return;
            }

            const groupViewField = {
                name: fieldDefinition.name,
                label: fieldDefinition.vname,
                type: fieldDefinition.type,
                fieldDefinition
            };

            const groupField = buildFieldFunction(record, groupViewField, language);
            addRecordFunction(record, fieldDefinition.name, groupField);
        });
    }

    /**
     * Is field initialized in record
     *
     * @param {object} record Record
     * @param {string} fieldName field
     * @returns {boolean} isInitialized
     */
    protected isFieldInitialized(record: Record, fieldName: string): boolean {
        return !!record.fields[fieldName];
    }

    /**
     * Is criteria field initialized in record
     *
     * @param {object} record Record
     * @param {string} fieldName field
     * @returns {boolean} isInitialized
     */
    protected isCriteriaFieldInitialized(record: SavedFilter, fieldName: string): boolean {
        return !!record.criteriaFields[fieldName];
    }

    /**
     * Parse value from record
     *
     * @param {object} viewField ViewFieldDefinition
     * @param {object} definition FieldDefinition
     * @param {object} record Record
     * @returns {object} value object
     */
    protected parseValue(
        viewField: ViewFieldDefinition,
        definition: FieldDefinition,
        record: Record
    ): { value: string; valueList: string[]; valueObject?: any } {

        const type = (viewField && viewField.type) || '';
        const source = (definition && definition.source) || '';
        const rname = (definition && definition.rname) || 'name';
        const viewName = viewField.name || '';
        let value: string;
        let valueList: string[] = null;

        if (!viewName || !record.attributes[viewName]) {
            value = '';
        } else if (type === 'relate' && source === 'non-db' && rname !== '') {
            value = record.attributes[viewName][rname];
            const valueObject = record.attributes[viewName];
            return {value, valueList, valueObject};
        } else {
            value = record.attributes[viewName];
        }

        if (Array.isArray(value)) {
            valueList = value;
            value = null;
        }

        return {value, valueList};
    }

    /**
     * Get save validators for the given field definition
     *
     * @param {object} record Record
     * @param {object} viewField ViewFieldDefinition
     * @returns {object} Validator map
     */
    protected getSaveValidators(
        record: Record,
        viewField: ViewFieldDefinition
    ): { validators: ValidatorFn[]; asyncValidators: AsyncValidatorFn[] } {

        const validators = this.validationManager.getSaveValidations(record.module, viewField, record);
        const asyncValidators = this.validationManager.getAsyncSaveValidations(record.module, viewField, record);
        return {validators, asyncValidators};
    }

    /**
     * Get Filter Validators for given field definition
     *
     * @param {object} record  Record
     * @param {object} viewField ViewFieldDefinition
     * @returns {object} validator map
     */
    protected getFilterValidators(
        record: SavedFilter,
        viewField: ViewFieldDefinition
    ): { validators: ValidatorFn[]; asyncValidators: AsyncValidatorFn[] } {

        const validators = this.validationManager.getFilterValidations(record.searchModule, viewField, record);
        const asyncValidators: AsyncValidatorFn[] = [];

        return {validators, asyncValidators};
    }

    /**
     * Build and initialize field object
     *
     * @param {string} module to use
     * @param {object} viewField ViewFieldDefinition
     * @param {string} value string
     * @param {[]} valueList string[]
     * @param {} valueObject value object
     * @param {object} record Record
     * @param {object} definition FieldDefinition
     * @param {[]} validators ValidatorFn[]
     * @param {[]} asyncValidators AsyncValidatorFn[]
     * @param {object} language LanguageStore
     * @returns {object} Field
     */
    protected setupField(
        module: string,
        viewField: ViewFieldDefinition,
        value: string,
        valueList: string[],
        valueObject: any,
        record: Record,
        definition: FieldDefinition,
        validators: ValidatorFn[],
        asyncValidators: AsyncValidatorFn[],
        language: LanguageStore
    ): Field {

        const formattedValue = this.typeFormatter.toUserFormat(viewField.type, value, {mode: 'edit'});

        const metadata = viewField.metadata || {};

        if (viewField.link) {
            metadata.link = viewField.link;
        }

        const field = {
            type: viewField.type,
            display: viewField.display,
            value,
            metadata,
            definition,
            labelKey: viewField.label,
            formControl: new FormControl(formattedValue, validators, asyncValidators),
            validators,
            asyncValidators
        } as Field;

        if (valueList) {
            field.valueList = valueList;
        }

        if (valueObject) {
            field.valueObject = valueObject;
        }

        if (language) {
            field.label = this.getFieldLabel(viewField.label, module, language);
        }

        if (viewField.label) {
            field.labelKey = viewField.label;
        }
        return field;
    }
}
