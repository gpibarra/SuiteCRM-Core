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

import {AfterViewInit, Component, ElementRef, Input, OnDestroy, OnInit, ViewChild} from '@angular/core';
import {combineLatest, Subscription} from 'rxjs';
import {RecordThreadStore} from '../../store/record-thread/record-thread.store';
import {RecordThreadStoreFactory} from '../../store/record-thread/record-thread.store.factory';
import {RecordThreadConfig} from './record-thread.model';
import {take} from 'rxjs/operators';
import {RecordThreadItemConfig} from '../record-thread-item/record-thread-item.model';
import {RecordThreadItemStore} from '../../store/record-thread/record-thread-item.store';
import {AttributeMap, ButtonInterface, Record, ViewMode} from 'common';
import {RecordThreadItemStoreFactory} from '../../store/record-thread/record-thread-item.store.factory';
import {RecordManager} from '../../../../services/record/record.manager';
import {MessageService} from '../../../../services/message/message.service';


@Component({
    selector: 'scrm-record-thread',
    templateUrl: './record-thread.component.html',
    styleUrls: [],
})
export class RecordThreadComponent implements OnInit, OnDestroy, AfterViewInit {

    @Input() config: RecordThreadConfig;
    @ViewChild('list') listContainer: ElementRef;

    store: RecordThreadStore;
    createStore: RecordThreadItemStore;
    records: RecordThreadItemStore[];
    loading = false;
    maxHeight = 400;
    direction: 'asc' | 'desc' = 'asc';
    protected shouldResetScroll = false;

    protected subs: Subscription[] = [];
    protected presetFieldValues: AttributeMap;

    constructor(
        protected storeFactory: RecordThreadStoreFactory,
        protected itemFactory: RecordThreadItemStoreFactory,
        protected recordManager: RecordManager,
        protected message: MessageService
    ) {
    }

    ngOnInit(): void {

        this.maxHeight = this.config.maxListHeight || this.maxHeight;

        if (!this.config.store) {
            this.store = this.storeFactory.create();
        }

        if (!this.config.module) {
            return;
        }

        this.direction = this.config.direction || this.direction;

        this.store.setMetadata(this.config.itemConfig.metadata);

        this.store.init(this.config.module, false);

        if (this.config.filters$) {
            this.subs.push(this.config.filters$.subscribe(filters => {
                this.store.setFilters(filters).pipe(take(1)).subscribe();
            }));
        }
        this.initCreate();

        this.subs.push(this.store.stores$.subscribe(records => {

            if (!this.records || !this.records.length) {
                this.shouldResetScroll = true;
            }

            if (this.direction === 'asc') {
                this.records = records.reverse();
                this.scheduleScrollReset();
                return;
            }

            this.records = records;
            this.scheduleScrollReset();
        }));

        this.initLoading();
    }

    ngAfterViewInit() {
        this.shouldResetScroll = true;
        this.resetScroll();
    }

    ngOnDestroy(): void {
        this.store.clear();
        this.store = null;
        this.subs.forEach(sub => sub.unsubscribe())
    }

    buildItem(item: RecordThreadItemStore, itemRef: any): RecordThreadItemConfig {
        return {
            ...this.config.itemConfig,
            store: item,
            klass: 'record-thread-list-item',
            expanded: (): void => {
                this.scrollToItem(itemRef);
            },
            collapsed: (): void => {
                this.scrollToItem(itemRef);
            }
        } as RecordThreadItemConfig;
    }

    getLoadMoreButton(): ButtonInterface {
        return {
            klass: 'load-more-button btn btn-link btn-sm',
            labelKey: 'LBL_LOAD_MORE',
            onClick: () => {
                this.store.loadMore();
            }
        } as ButtonInterface;
    }

    buildCreateItem(): RecordThreadItemConfig {
        return {
            ...this.config.createConfig,
            store: this.createStore,
            rowClass: {'pt-1': true},
            klass: 'record-thread-create-item',
        } as RecordThreadItemConfig;
    }

    getCreateButton(): ButtonInterface {
        return {
            klass: 'create-thread-item-button btn btn-danger btn-sm',
            labelKey: 'LBL_SUBMIT_BUTTON_LABEL',
            onClick: () => {
                this.createStore.validate().pipe(take(1)).subscribe(valid => {
                    if (valid) {
                        this.createStore.save().pipe(take(1)).subscribe(() => {
                            this.store.reload();
                            this.initRecord();

                            this.shouldResetScroll = true;

                            this.message.addSuccessMessageByKey('LBL_ACTION_SUCCESS')
                        });
                        return;
                    }

                    this.message.addWarningMessageByKey('LBL_VALIDATION_ERRORS');
                });
            }
        } as ButtonInterface;
    }

    allLoaded(): boolean {
        return !!(this.store && this.store.allLoaded());
    }

    protected initRecord() {
        const emptyRecord = this.recordManager.buildEmptyRecord(this.config.module);
        this.addPresetFields(emptyRecord);

        let mode = 'edit' as ViewMode;
        if (this.config.createConfig && this.config.createConfig.initialMode) {
            mode = this.config.createConfig.initialMode;
        }

        this.createStore.initRecord(emptyRecord, mode, false);
    }

    protected scrollToEnd(): void {
        if (!this.listContainer || !this.listContainer.nativeElement) {
            return;
        }

        this.scrollTo(this.listContainer.nativeElement.scrollHeight);
    }

    protected scrollToTop(): void {
        this.scrollTo(0);
    }

    protected scrollTo(position: number): void {
        try {
            this.listContainer.nativeElement.scrollTop = position;
        } catch (err) {
        }
    }

    protected scrollToItem(item: any) {
        if (!item || !this.listContainer || !this.listContainer.nativeElement) {
            return;
        }

        const elementTop = item.offsetTop;
        const parentTop = this.listContainer.nativeElement.offsetTop;
        const relativeTop = elementTop - parentTop;

        this.scrollTo(relativeTop);
    }

    protected resetScroll(): void {
        if (this.shouldResetScroll === false) {
            return;
        }

        if (this.direction === 'asc') {
            this.scrollToEnd();
        } else {
            this.scrollToTop();
        }

        this.shouldResetScroll = false;
    }

    protected scheduleScrollReset(): void {
        setTimeout(() => {
            this.resetScroll();
        }, 500);
    }

    protected initCreate() {
        if (!this.config.create) {
            return;
        }

        this.createStore = this.itemFactory.create();
        this.createStore.setMetadata(this.config.createConfig.metadata);
        this.initRecord();
        this.initPresetFieldsMapping();
    }

    protected initPresetFieldsMapping() {

        if (!this.config.presetFields$) {
            return;
        }

        this.subs.push(this.config.presetFields$.subscribe(presetFieldValues => {

            if (!presetFieldValues || !Object.keys(presetFieldValues).length) {
                return;
            }

            this.presetFieldValues = presetFieldValues;

            const record = this.createStore.recordStore.getBaseRecord();
            this.addPresetFields(record);

            this.createStore.recordStore.setRecord(record);
        }));
    }

    protected addPresetFields(record: Record): void {
        if (!this.presetFieldValues) {
            return;
        }

        record.attributes = {
            ...this.presetFieldValues,
            ...(record.attributes || {})
        };
    }

    protected initLoading(): void {
        const loading = [
            this.store.$loading
        ];

        if (this.createStore && this.createStore.loading$) {
            loading.push(this.createStore.loading$)
        }

        const $loading = combineLatest(loading);
        this.subs.push($loading.subscribe((loadings) => {

            if (!loadings || !loadings.length) {
                this.loading = false;
                return;
            }

            let loading = false;

            loadings.forEach(value => {
                loading = loading || value;
            });

            this.loading = loading;
        }));
    }
}
