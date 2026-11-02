<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261101231237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, pin_hash VARCHAR(255) NOT NULL, last_login_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_AD8A54A9F85E0677 (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE audit_log (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(80) NOT NULL, entity_type VARCHAR(80) DEFAULT NULL, entity_id INT DEFAULT NULL, details LONGTEXT DEFAULT NULL, actor VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE employee (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(40) DEFAULT NULL, address LONGTEXT DEFAULT NULL, ni_number VARCHAR(20) DEFAULT NULL, job_title VARCHAR(120) DEFAULT NULL, department VARCHAR(120) DEFAULT NULL, employment_start_date DATE DEFAULT NULL, employment_end_date DATE DEFAULT NULL, status VARCHAR(20) NOT NULL, hourly_rate DOUBLE PRECISION NOT NULL, tax_code VARCHAR(20) NOT NULL, ni_category VARCHAR(2) NOT NULL, pension_opt_in TINYINT NOT NULL, pension_employee_rate DOUBLE PRECISION DEFAULT NULL, pension_employer_rate DOUBLE PRECISION DEFAULT NULL, student_loan_plan VARCHAR(20) NOT NULL, pay_frequency VARCHAR(20) NOT NULL, contracted_weekly_hours DOUBLE PRECISION NOT NULL, holiday_entitlement_weeks DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE holiday_booking (id INT AUTO_INCREMENT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, hours DOUBLE PRECISION NOT NULL, status VARCHAR(20) NOT NULL, note LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, cancelled_at DATETIME DEFAULT NULL, employee_id INT NOT NULL, INDEX IDX_D2D1A8D08C03F15C (employee_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE holiday_ledger_entry (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(20) NOT NULL, hours DOUBLE PRECISION NOT NULL, description VARCHAR(255) NOT NULL, effective_date DATE NOT NULL, created_at DATETIME NOT NULL, employee_id INT NOT NULL, payroll_run_id INT DEFAULT NULL, holiday_booking_id INT DEFAULT NULL, INDEX IDX_1FFCCE158C03F15C (employee_id), INDEX IDX_1FFCCE15A5FC78E (payroll_run_id), INDEX IDX_1FFCCE157CC22471 (holiday_booking_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE payroll_run (id INT AUTO_INCREMENT NOT NULL, frequency VARCHAR(20) NOT NULL, period_start DATE NOT NULL, period_end DATE NOT NULL, pay_date DATE NOT NULL, status VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, gross_total DOUBLE PRECISION NOT NULL, paye_total DOUBLE PRECISION NOT NULL, employee_ni_total DOUBLE PRECISION NOT NULL, employer_ni_total DOUBLE PRECISION NOT NULL, pension_employee_total DOUBLE PRECISION NOT NULL, pension_employer_total DOUBLE PRECISION NOT NULL, student_loan_total DOUBLE PRECISION NOT NULL, net_total DOUBLE PRECISION NOT NULL, employer_cost_total DOUBLE PRECISION NOT NULL, employment_allowance_applied DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, finalised_at DATETIME DEFAULT NULL, tax_year_id INT NOT NULL, INDEX IDX_7BA664751895EAD (tax_year_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE payslip (id INT AUTO_INCREMENT NOT NULL, hours_worked DOUBLE PRECISION NOT NULL, overtime_hours DOUBLE PRECISION NOT NULL, bonus DOUBLE PRECISION NOT NULL, tax_code VARCHAR(20) NOT NULL, ni_category VARCHAR(2) NOT NULL, hourly_rate DOUBLE PRECISION NOT NULL, basic_pay DOUBLE PRECISION NOT NULL, overtime_pay DOUBLE PRECISION NOT NULL, gross_pay DOUBLE PRECISION NOT NULL, paye DOUBLE PRECISION NOT NULL, employee_ni DOUBLE PRECISION NOT NULL, employer_ni DOUBLE PRECISION NOT NULL, pension_employee DOUBLE PRECISION NOT NULL, pension_employer DOUBLE PRECISION NOT NULL, student_loan DOUBLE PRECISION NOT NULL, net_pay DOUBLE PRECISION NOT NULL, employer_cost DOUBLE PRECISION NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, payroll_run_id INT NOT NULL, employee_id INT NOT NULL, UNIQUE INDEX uniq_payslip_run_employee (payroll_run_id, employee_id), INDEX IDX_9A13CDF0A5FC78E (payroll_run_id), INDEX IDX_9A13CDF08C03F15C (employee_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tax_year (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, effective_from DATE NOT NULL, effective_to DATE NOT NULL, active TINYINT NOT NULL, personal_allowance DOUBLE PRECISION NOT NULL, basic_rate DOUBLE PRECISION NOT NULL, basic_rate_limit DOUBLE PRECISION NOT NULL, higher_rate DOUBLE PRECISION NOT NULL, higher_rate_limit DOUBLE PRECISION NOT NULL, additional_rate DOUBLE PRECISION NOT NULL, ni_primary_threshold DOUBLE PRECISION NOT NULL, ni_upper_earnings_limit DOUBLE PRECISION NOT NULL, ni_employee_rate DOUBLE PRECISION NOT NULL, ni_employee_upper_rate DOUBLE PRECISION NOT NULL, ni_employer_rate DOUBLE PRECISION NOT NULL, employer_ni_threshold DOUBLE PRECISION NOT NULL, pension_lower DOUBLE PRECISION NOT NULL, pension_upper DOUBLE PRECISION NOT NULL, pension_employee_rate DOUBLE PRECISION NOT NULL, pension_employer_rate DOUBLE PRECISION NOT NULL, student_loan_plan1_threshold DOUBLE PRECISION NOT NULL, student_loan_plan2_threshold DOUBLE PRECISION NOT NULL, student_loan_plan4_threshold DOUBLE PRECISION NOT NULL, student_loan_plan5_threshold DOUBLE PRECISION NOT NULL, student_loan_postgrad_threshold DOUBLE PRECISION NOT NULL, student_loan_rate DOUBLE PRECISION NOT NULL, student_loan_postgrad_rate DOUBLE PRECISION NOT NULL, holiday_accrual_rate DOUBLE PRECISION NOT NULL, employment_allowance DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, activated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE holiday_booking ADD CONSTRAINT FK_D2D1A8D08C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE holiday_ledger_entry ADD CONSTRAINT FK_1FFCCE158C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE holiday_ledger_entry ADD CONSTRAINT FK_1FFCCE15A5FC78E FOREIGN KEY (payroll_run_id) REFERENCES payroll_run (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE holiday_ledger_entry ADD CONSTRAINT FK_1FFCCE157CC22471 FOREIGN KEY (holiday_booking_id) REFERENCES holiday_booking (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE payroll_run ADD CONSTRAINT FK_7BA664751895EAD FOREIGN KEY (tax_year_id) REFERENCES tax_year (id)');
        $this->addSql('ALTER TABLE payslip ADD CONSTRAINT FK_9A13CDF0A5FC78E FOREIGN KEY (payroll_run_id) REFERENCES payroll_run (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE payslip ADD CONSTRAINT FK_9A13CDF08C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE holiday_booking DROP FOREIGN KEY FK_D2D1A8D08C03F15C');
        $this->addSql('ALTER TABLE holiday_ledger_entry DROP FOREIGN KEY FK_1FFCCE158C03F15C');
        $this->addSql('ALTER TABLE holiday_ledger_entry DROP FOREIGN KEY FK_1FFCCE15A5FC78E');
        $this->addSql('ALTER TABLE holiday_ledger_entry DROP FOREIGN KEY FK_1FFCCE157CC22471');
        $this->addSql('ALTER TABLE payroll_run DROP FOREIGN KEY FK_7BA664751895EAD');
        $this->addSql('ALTER TABLE payslip DROP FOREIGN KEY FK_9A13CDF0A5FC78E');
        $this->addSql('ALTER TABLE payslip DROP FOREIGN KEY FK_9A13CDF08C03F15C');
        $this->addSql('DROP TABLE admin_user');
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('DROP TABLE employee');
        $this->addSql('DROP TABLE holiday_booking');
        $this->addSql('DROP TABLE holiday_ledger_entry');
        $this->addSql('DROP TABLE payroll_run');
        $this->addSql('DROP TABLE payslip');
        $this->addSql('DROP TABLE tax_year');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
