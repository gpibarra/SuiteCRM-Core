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

import {ComponentFixture, TestBed, waitForAsync} from '@angular/core/testing';

import {TableComponent} from './table.component';
import {TableHeaderModule} from '@components/table/table-header/table-header.module';
import {TableBodyModule} from '@components/table/table-body/table-body.module';
import {TableFooterModule} from '@components/table/table-footer/table-footer.module';
import {AngularSvgIconModule} from 'angular-svg-icon';
import {HttpClientTestingModule} from '@angular/common/http/testing';
import {ApolloTestingModule} from 'apollo-angular/testing';
import {ImageModule} from '@components/image/image.module';
import {ThemeImagesStore} from 'core';
import {of} from 'rxjs';
import {themeImagesMockData} from 'core';
import {take} from 'rxjs/operators';
import {ListViewStore} from '@views/list/store/list-view/list-view.store';
import {listviewStoreMock} from '@views/list/store/list-view/list-view.store.spec.mock';
import {MetadataStore} from 'core';
import {metadataStoreMock} from 'core';
import {SortButtonModule} from '@components/sort-button/sort-button.module';
import {LanguageStore} from 'core';
import {languageStoreMock} from 'core';
import {RouterTestingModule} from '@angular/router/testing';
import {Component} from '@angular/core';
import {tableConfigMock} from '@components/table/table.component.spec.mock';

@Component({
    selector: 'table-test-host-component',
    template: '<scrm-table [config]="tableConfig"></scrm-table>'
})
class TableTestHostComponent {
    tableConfig = tableConfigMock;
}

describe('TableComponent', () => {
    let component: TableTestHostComponent;
    let fixture: ComponentFixture<TableTestHostComponent>;

    beforeEach(waitForAsync(() => {
        TestBed.configureTestingModule({
            imports: [
                TableHeaderModule,
                TableBodyModule,
                TableFooterModule,
                AngularSvgIconModule.forRoot(),
                HttpClientTestingModule,
                ApolloTestingModule,
                ImageModule,
                SortButtonModule,
                RouterTestingModule
            ],
            declarations: [TableComponent],
            providers: [
                {
                    provide: ListViewStore, useValue: listviewStoreMock
                },
                {
                    provide: LanguageStore, useValue: languageStoreMock
                },
                {
                    provide: ThemeImagesStore, useValue: {
                        images$: of(themeImagesMockData).pipe(take(1))
                    }
                },
                {
                    provide: MetadataStore, useValue: metadataStoreMock
                },
            ],
        })
            .compileComponents();
    }));

    beforeEach(() => {
        fixture = TestBed.createComponent(TableTestHostComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});