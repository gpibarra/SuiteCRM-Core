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

import {Injectable} from '@angular/core';
import {FieldDefinitionMap, FieldMap, isVoid, Record, ViewFieldDefinition} from 'common';
import {FormGroup} from '@angular/forms';
import {LanguageStore} from '../../store/language/language.store';
import {FieldManager} from './field/field.manager';
import {Params} from '@angular/router';

@Injectable({
    providedIn: 'root'
})
export class RecordManager {

    constructor(
        protected fieldManager: FieldManager,
        protected language: LanguageStore
    ) {
    }

    /**
     * Get empty record
     *
     * @param {string} module string
     * @returns {object} Record
     */
    buildEmptyRecord(module: string): Record {
        return {
            id: '',
            module,
            attributes: {
                id: ''
            },
            fields: {},
            formGroup: new FormGroup({}),
        } as Record;
    }

    /**
     * Init Fields
     *
     * @param {object} record to use
     * @param {object} viewFieldDefinitions to use
     * @returns {object} fields
     */
    public initFields(record: Record, viewFieldDefinitions: ViewFieldDefinition[]): FieldMap {

        if (!record.fields) {
            record.fields = {} as FieldMap;
        }

        if (!record.formGroup) {
            record.formGroup = new FormGroup({});
        }

        viewFieldDefinitions.forEach(viewField => {
            if (!viewField || !viewField.name) {
                return;
            }
            this.fieldManager.addField(record, viewField, this.language);
        });

        return record.fields;
    }

    /**
     * Inject param fields
     *
     * @param {object} params Params
     * @param {object} record Record
     * @param {object} vardefs FieldDefinitionMap
     */
    public injectParamFields(params: Params, record: Record, vardefs: FieldDefinitionMap): void {

        Object.keys(params).forEach(paramKey => {

            const definition = vardefs[paramKey];

            if (!isVoid(definition)) {
                const type = definition.type || '';
                const idName = definition.id_name || '';
                const name = definition.name || '';
                const rname = definition.rname || '';

                if (type === 'relate' && idName === name) {
                    record.attributes[paramKey] = params[paramKey];
                    return;
                }

                if (type === 'relate') {
                    const relate = {} as any;

                    if (rname) {
                        relate[rname] = params[paramKey];
                    }

                    if (idName && params[idName]) {
                        relate.id = params[idName];
                    }

                    record.attributes[paramKey] = relate;

                    return;
                }

                record.attributes[paramKey] = params[paramKey];

                return;
            }

        });
    }
}
